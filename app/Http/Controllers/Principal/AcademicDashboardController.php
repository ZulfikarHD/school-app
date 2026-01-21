<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\GradeCalculationService;
use App\Services\ReportCardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * AcademicDashboardController - Controller untuk Principal menampilkan
 * dashboard akademik dengan analytics nilai per mapel, distribusi predikat,
 * dan rekap nilai semua kelas
 */
class AcademicDashboardController extends Controller
{
    /**
     * KKM (Kriteria Ketuntasan Minimal) default untuk semua mapel
     */
    protected const KKM_DEFAULT = 75;

    public function __construct(
        protected GradeCalculationService $gradeCalculationService,
        protected ReportCardService $reportCardService
    ) {}

    /**
     * Menampilkan dashboard akademik dengan analytics
     * yang mencakup rata-rata nilai per mapel, distribusi predikat,
     * dan highlight mapel dengan rata-rata di bawah KKM
     */
    public function index(Request $request): Response
    {
        $tahunAjaran = $request->input('tahun_ajaran', $this->reportCardService->getCurrentTahunAjaran());
        $semester = $request->input('semester', $this->reportCardService->getCurrentSemester());
        $classId = $request->input('class_id');

        // Get kelas aktif untuk filter
        $classes = SchoolClass::query()
            ->active()
            ->byAcademicYear($tahunAjaran)
            ->withCount('students')
            ->with('waliKelas:id,name')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->nama_lengkap,
                'student_count' => $c->students_count,
                'wali_kelas' => $c->waliKelas?->name ?? '-',
            ]);

        // Get mata pelajaran
        $subjects = Subject::where('is_active', true)
            ->orderBy('nama_mapel')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->nama_mapel,
                'code' => $s->kode_mapel,
            ]);

        // Get data untuk analytics
        $subjectAverages = $this->getSubjectAverages($tahunAjaran, $semester, $classId);
        $predikatDistribution = $this->getPredikatDistribution($tahunAjaran, $semester, $classId);
        $belowKKMSubjects = $this->getBelowKKMSubjects($subjectAverages);
        $overallStats = $this->getOverallStats($tahunAjaran, $semester, $classId);
        $reportCardStats = $this->getReportCardStats($tahunAjaran, $semester, $classId);

        return Inertia::render('Principal/Academic/Dashboard', [
            'title' => 'Dashboard Akademik',
            'filters' => [
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
                'class_id' => $classId,
            ],
            'classes' => $classes,
            'subjects' => $subjects,
            'subjectAverages' => $subjectAverages,
            'predikatDistribution' => $predikatDistribution,
            'belowKKMSubjects' => $belowKKMSubjects,
            'overallStats' => $overallStats,
            'reportCardStats' => $reportCardStats,
            'kkm' => self::KKM_DEFAULT,
            'availableTahunAjaran' => $this->reportCardService->getAvailableTahunAjaran() ?: [$this->reportCardService->getCurrentTahunAjaran()],
            'semesters' => [
                ['value' => '1', 'label' => 'Semester 1 (Ganjil)'],
                ['value' => '2', 'label' => 'Semester 2 (Genap)'],
            ],
        ]);
    }

    /**
     * Menampilkan rekap nilai semua kelas dengan drill-down per siswa
     */
    public function grades(Request $request): Response
    {
        $tahunAjaran = $request->input('tahun_ajaran', $this->reportCardService->getCurrentTahunAjaran());
        $semester = $request->input('semester', $this->reportCardService->getCurrentSemester());
        $classId = $request->input('class_id');

        // Get kelas aktif untuk filter
        $classes = SchoolClass::query()
            ->active()
            ->byAcademicYear($tahunAjaran)
            ->withCount('students')
            ->with('waliKelas:id,name')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->nama_lengkap,
                'tingkat' => $c->tingkat,
                'student_count' => $c->students_count,
                'wali_kelas' => $c->waliKelas?->name ?? '-',
            ]);

        // Get grade summary per kelas
        $classSummaries = [];

        if ($classId) {
            // Single class detail view
            $classSummary = $this->gradeCalculationService->getClassGradeSummary(
                $classId,
                $tahunAjaran,
                $semester
            );
            $classSummaries[] = $classSummary;
        } else {
            // Overview semua kelas
            foreach ($classes as $class) {
                $summary = $this->getClassOverview($class['id'], $tahunAjaran, $semester);
                $classSummaries[] = $summary;
            }
        }

        return Inertia::render('Principal/Academic/Grades', [
            'title' => 'Rekap Nilai',
            'filters' => [
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
                'class_id' => $classId,
            ],
            'classes' => $classes,
            'classSummaries' => $classSummaries,
            'isDetailView' => (bool) $classId,
            'availableTahunAjaran' => $this->reportCardService->getAvailableTahunAjaran() ?: [$this->reportCardService->getCurrentTahunAjaran()],
            'semesters' => [
                ['value' => '1', 'label' => 'Semester 1 (Ganjil)'],
                ['value' => '2', 'label' => 'Semester 2 (Genap)'],
            ],
        ]);
    }

    /**
     * Helper: Mendapatkan rata-rata nilai per mata pelajaran
     *
     * @return array<int, array{subject_id: int, subject_name: string, average: float, predikat: string}>
     */
    protected function getSubjectAverages(string $tahunAjaran, string $semester, ?int $classId): array
    {
        $subjects = Subject::where('is_active', true)->get();
        $results = [];

        foreach ($subjects as $subject) {
            $query = Grade::query()
                ->bySubject($subject->id)
                ->byTahunAjaran($tahunAjaran)
                ->bySemester($semester);

            if ($classId) {
                $query->byClass($classId);
            }

            $average = $query->avg('score') ?? 0;
            $average = round($average, 2);

            $results[] = [
                'subject_id' => $subject->id,
                'subject_name' => $subject->nama_mapel,
                'subject_code' => $subject->kode_mapel,
                'average' => $average,
                'predikat' => Grade::getPredikatFromScore($average),
                'is_below_kkm' => $average < self::KKM_DEFAULT && $average > 0,
            ];
        }

        // Sort by average descending
        usort($results, fn ($a, $b) => $b['average'] <=> $a['average']);

        return $results;
    }

    /**
     * Helper: Mendapatkan distribusi predikat A/B/C/D
     *
     * @return array{A: int, B: int, C: int, D: int, total: int}
     */
    protected function getPredikatDistribution(string $tahunAjaran, string $semester, ?int $classId): array
    {
        $query = ReportCard::query()
            ->byTahunAjaran($tahunAjaran)
            ->bySemester($semester)
            ->whereNotNull('average_score');

        if ($classId) {
            $query->byClass($classId);
        }

        $reportCards = $query->get();

        $distribution = [
            'A' => 0,
            'B' => 0,
            'C' => 0,
            'D' => 0,
        ];

        foreach ($reportCards as $rc) {
            $predikat = Grade::getPredikatFromScore($rc->average_score);
            if (isset($distribution[$predikat])) {
                $distribution[$predikat]++;
            }
        }

        $distribution['total'] = $reportCards->count();

        return $distribution;
    }

    /**
     * Helper: Mendapatkan mata pelajaran dengan rata-rata di bawah KKM
     *
     * @param  array<int, array>  $subjectAverages
     * @return array<int, array>
     */
    protected function getBelowKKMSubjects(array $subjectAverages): array
    {
        return array_values(array_filter(
            $subjectAverages,
            fn ($item) => $item['is_below_kkm']
        ));
    }

    /**
     * Helper: Mendapatkan statistik overall
     *
     * @return array{total_students: int, total_grades: int, overall_average: float}
     */
    protected function getOverallStats(string $tahunAjaran, string $semester, ?int $classId): array
    {
        $gradeQuery = Grade::query()
            ->byTahunAjaran($tahunAjaran)
            ->bySemester($semester);

        $reportCardQuery = ReportCard::query()
            ->byTahunAjaran($tahunAjaran)
            ->bySemester($semester);

        if ($classId) {
            $gradeQuery->byClass($classId);
            $reportCardQuery->byClass($classId);
        }

        $totalGrades = $gradeQuery->count();
        $overallAverage = $gradeQuery->avg('score') ?? 0;
        $totalStudents = $reportCardQuery->distinct('student_id')->count('student_id');

        return [
            'total_students' => $totalStudents,
            'total_grades' => $totalGrades,
            'overall_average' => round($overallAverage, 2),
        ];
    }

    /**
     * Helper: Mendapatkan statistik rapor
     *
     * @return array{total: int, draft: int, pending: int, approved: int, released: int}
     */
    protected function getReportCardStats(string $tahunAjaran, string $semester, ?int $classId): array
    {
        $query = ReportCard::query()
            ->byTahunAjaran($tahunAjaran)
            ->bySemester($semester);

        if ($classId) {
            $query->byClass($classId);
        }

        $total = (clone $query)->count();
        $draft = (clone $query)->where('status', ReportCard::STATUS_DRAFT)->count();
        $pending = (clone $query)->where('status', ReportCard::STATUS_PENDING_APPROVAL)->count();
        $approved = (clone $query)->where('status', ReportCard::STATUS_APPROVED)->count();
        $released = (clone $query)->where('status', ReportCard::STATUS_RELEASED)->count();

        return [
            'total' => $total,
            'draft' => $draft,
            'pending' => $pending,
            'approved' => $approved,
            'released' => $released,
        ];
    }

    /**
     * Helper: Mendapatkan overview kelas (untuk list view)
     *
     * @return array{class_id: int, class_name: string, student_count: int, average: float, highest: float, lowest: float}
     */
    protected function getClassOverview(int $classId, string $tahunAjaran, string $semester): array
    {
        $class = SchoolClass::with('waliKelas:id,name')->find($classId);

        $reportCards = ReportCard::query()
            ->byClass($classId)
            ->byTahunAjaran($tahunAjaran)
            ->bySemester($semester)
            ->whereNotNull('average_score')
            ->get();

        $averages = $reportCards->pluck('average_score')->filter();

        return [
            'class_id' => $classId,
            'class_name' => $class?->nama_lengkap ?? '-',
            'wali_kelas' => $class?->waliKelas?->name ?? '-',
            'student_count' => $reportCards->count(),
            'average' => $averages->count() > 0 ? round($averages->avg(), 2) : 0,
            'highest' => $averages->count() > 0 ? round($averages->max(), 2) : 0,
            'lowest' => $averages->count() > 0 ? round($averages->min(), 2) : 0,
            'predikat_distribution' => [
                'A' => $reportCards->filter(fn ($rc) => Grade::getPredikatFromScore($rc->average_score) === 'A')->count(),
                'B' => $reportCards->filter(fn ($rc) => Grade::getPredikatFromScore($rc->average_score) === 'B')->count(),
                'C' => $reportCards->filter(fn ($rc) => Grade::getPredikatFromScore($rc->average_score) === 'C')->count(),
                'D' => $reportCards->filter(fn ($rc) => Grade::getPredikatFromScore($rc->average_score) === 'D')->count(),
            ],
        ];
    }
}
