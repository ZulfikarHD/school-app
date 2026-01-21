<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * GradeController - Controller untuk Admin mengelola dan melihat rekap nilai
 * yang mencakup list semua nilai dan summary per kelas/siswa
 */
class GradeController extends Controller
{
    public function __construct(
        protected GradeCalculationService $gradeService
    ) {}

    /**
     * Menampilkan halaman index dengan list semua nilai
     * beserta filter dan statistik
     */
    public function index(Request $request): Response
    {
        $tahunAjaran = $request->input('tahun_ajaran', $this->getCurrentTahunAjaran());
        $semester = $request->input('semester');
        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $search = $request->input('search');

        // Build query untuk grades dengan grouping per assessment
        $query = Grade::query()
            ->with(['class', 'subject', 'teacher:id,name'])
            ->byTahunAjaran($tahunAjaran)
            ->select([
                'class_id',
                'subject_id',
                'teacher_id',
                'tahun_ajaran',
                'semester',
                'assessment_type',
                'title',
            ])
            ->selectRaw('COUNT(DISTINCT student_id) as student_count')
            ->selectRaw('AVG(score) as average_score')
            ->selectRaw('MAX(updated_at) as last_updated')
            ->groupBy([
                'class_id',
                'subject_id',
                'teacher_id',
                'tahun_ajaran',
                'semester',
                'assessment_type',
                'title',
            ]);

        if ($semester) {
            $query->where('semester', $semester);
        }

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        $grades = $query
            ->orderByDesc('last_updated')
            ->paginate(15)
            ->withQueryString();

        // Transform data untuk frontend
        $grades->through(function ($grade) {
            return [
                'class_id' => $grade->class_id,
                'class_name' => $grade->class?->nama_lengkap ?? '-',
                'subject_id' => $grade->subject_id,
                'subject_name' => $grade->subject?->nama_mapel ?? '-',
                'teacher_name' => $grade->teacher?->name ?? '-',
                'semester' => $grade->semester,
                'assessment_type' => $grade->assessment_type,
                'assessment_type_label' => Grade::getAssessmentTypes()[$grade->assessment_type] ?? $grade->assessment_type,
                'title' => $grade->title,
                'student_count' => $grade->student_count,
                'average_score' => round($grade->average_score, 2),
                'last_updated' => $grade->last_updated
                    ? \Carbon\Carbon::parse($grade->last_updated)->format('d M Y H:i')
                    : '-',
            ];
        });

        // Get filter options
        $classes = SchoolClass::query()
            ->active()
            ->byAcademicYear($tahunAjaran)
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->nama_lengkap,
            ]);

        $subjects = Subject::query()
            ->active()
            ->orderBy('nama_mapel')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->nama_mapel,
            ]);

        // Get statistics
        $stats = $this->getGradeStatistics($tahunAjaran, $semester);

        return Inertia::render('Admin/Grades/Index', [
            'grades' => $grades,
            'filters' => [
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'search' => $search,
            ],
            'classes' => $classes,
            'subjects' => $subjects,
            'assessmentTypes' => Grade::getAssessmentTypes(),
            'statistics' => $stats,
            'availableTahunAjaran' => $this->getAvailableTahunAjaran(),
        ]);
    }

    /**
     * Menampilkan halaman summary/rekap nilai per kelas dengan
     * tabel lengkap nilai siswa dan export Excel
     */
    public function summary(Request $request): Response
    {
        $tahunAjaran = $request->input('tahun_ajaran', $this->getCurrentTahunAjaran());
        $semester = $request->input('semester', '1');
        $classId = $request->input('class_id');

        $summaryData = null;

        if ($classId) {
            $summaryData = $this->gradeService->getClassGradeSummary(
                (int) $classId,
                $tahunAjaran,
                $semester
            );
        }

        // Get filter options
        $classes = SchoolClass::query()
            ->active()
            ->byAcademicYear($tahunAjaran)
            ->with('waliKelas:id,name')
            ->withCount('students')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->nama_lengkap,
                'wali_kelas' => $c->waliKelas?->name ?? '-',
                'student_count' => $c->students_count,
            ]);

        return Inertia::render('Admin/Grades/Summary', [
            'summary' => $summaryData,
            'filters' => [
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
                'class_id' => $classId,
            ],
            'classes' => $classes,
            'availableTahunAjaran' => $this->getAvailableTahunAjaran(),
        ]);
    }

    /**
     * Export rekap nilai ke Excel untuk kelas tertentu
     */
    public function export(Request $request): StreamedResponse
    {
        $tahunAjaran = $request->input('tahun_ajaran', $this->getCurrentTahunAjaran());
        $semester = $request->input('semester', '1');
        $classId = $request->input('class_id');

        if (! $classId) {
            abort(400, 'Class ID is required');
        }

        $summaryData = $this->gradeService->getClassGradeSummary(
            (int) $classId,
            $tahunAjaran,
            $semester
        );

        $className = $summaryData['class']['name'] ?? 'Unknown';
        $filename = "rekap_nilai_{$className}_semester_{$semester}_{$tahunAjaran}.csv";
        $filename = str_replace(['/', ' '], ['_', '_'], $filename);

        return response()->streamDownload(function () use ($summaryData) {
            $output = fopen('php://output', 'w');

            // BOM for UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            $headers = ['No', 'NIS', 'Nama Siswa'];
            foreach ($summaryData['subjects'] as $subject) {
                $headers[] = $subject['name'].' (Nilai)';
                $headers[] = $subject['name'].' (Predikat)';
            }
            $headers[] = 'Rata-Rata';
            $headers[] = 'Ranking';
            fputcsv($output, $headers);

            // Data rows
            $no = 1;
            foreach ($summaryData['students'] as $student) {
                $row = [
                    $no++,
                    $student['nis'],
                    $student['name'],
                ];

                foreach ($summaryData['subjects'] as $subject) {
                    $subjectData = $student['subjects'][$subject['id']] ?? null;
                    $row[] = $subjectData ? $subjectData['final_grade'] : '-';
                    $row[] = $subjectData ? $subjectData['predikat'] : '-';
                }

                $row[] = $student['overall_average'];
                $row[] = $student['rank'];
                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Helper: Mendapatkan statistik nilai keseluruhan
     */
    protected function getGradeStatistics(string $tahunAjaran, ?string $semester): array
    {
        $query = Grade::query()->byTahunAjaran($tahunAjaran);

        if ($semester) {
            $query->bySemester($semester);
        }

        $totalGrades = $query->count();
        $averageScore = $query->avg('score');
        $classCount = $query->distinct('class_id')->count('class_id');
        $subjectCount = $query->distinct('subject_id')->count('subject_id');

        // Distribusi predikat
        $allScores = Grade::query()
            ->byTahunAjaran($tahunAjaran)
            ->when($semester, fn ($q) => $q->bySemester($semester))
            ->pluck('score');

        $distribution = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0];
        foreach ($allScores as $score) {
            $predikat = Grade::getPredikatFromScore($score);
            $distribution[$predikat]++;
        }

        return [
            'total_grades' => $totalGrades,
            'average_score' => $averageScore ? round($averageScore, 2) : 0,
            'class_count' => $classCount,
            'subject_count' => $subjectCount,
            'distribution' => $distribution,
        ];
    }

    /**
     * Helper: Mendapatkan tahun ajaran saat ini
     */
    protected function getCurrentTahunAjaran(): string
    {
        $now = now();
        $year = $now->year;
        $month = $now->month;

        if ($month >= 7) {
            return $year.'/'.($year + 1);
        }

        return ($year - 1).'/'.$year;
    }

    /**
     * Helper: Mendapatkan list tahun ajaran yang tersedia
     */
    protected function getAvailableTahunAjaran(): array
    {
        $tahunAjaran = Grade::query()
            ->select('tahun_ajaran')
            ->distinct()
            ->orderByDesc('tahun_ajaran')
            ->pluck('tahun_ajaran')
            ->toArray();

        $current = $this->getCurrentTahunAjaran();
        if (! in_array($current, $tahunAjaran)) {
            array_unshift($tahunAjaran, $current);
        }

        return $tahunAjaran;
    }
}
