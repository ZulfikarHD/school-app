<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\GradeWeightConfig;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Collection;

/**
 * GradeCalculationService - Service untuk menghitung nilai akhir siswa
 * berdasarkan komponen penilaian (UH, UTS, UAS, Praktik) dengan bobot
 * yang telah dikonfigurasi sesuai Kurikulum 2013 (K13)
 */
class GradeCalculationService
{
    /**
     * Menghitung nilai akhir siswa untuk suatu mata pelajaran
     * dengan mengaplikasikan formula bobot K13
     *
     * @param  int  $studentId  ID siswa
     * @param  int  $subjectId  ID mata pelajaran
     * @param  string  $tahunAjaran  Tahun ajaran (e.g., "2024/2025")
     * @param  string  $semester  Semester (1 atau 2)
     * @return array{final_grade: float, predikat: string, predikat_label: string, breakdown: array}
     */
    public function calculateFinalGrade(
        int $studentId,
        int $subjectId,
        string $tahunAjaran,
        string $semester
    ): array {
        // Ambil konfigurasi bobot aktif
        $weightConfig = GradeWeightConfig::getActiveConfig($tahunAjaran, $subjectId)
            ?? GradeWeightConfig::getOrCreateDefault($tahunAjaran);

        // Ambil semua nilai siswa untuk mapel ini
        $grades = Grade::query()
            ->byStudent($studentId)
            ->bySubject($subjectId)
            ->byTahunAjaran($tahunAjaran)
            ->bySemester($semester)
            ->get();

        // Hitung rata-rata per komponen
        $uhAverage = $this->calculateComponentAverage($grades, Grade::ASSESSMENT_UH);
        $utsScore = $this->getLatestScore($grades, Grade::ASSESSMENT_UTS);
        $uasScore = $this->getLatestScore($grades, Grade::ASSESSMENT_UAS);
        $praktikAverage = $this->calculateComponentAverage($grades, [
            Grade::ASSESSMENT_PRAKTIK,
            Grade::ASSESSMENT_PROYEK,
        ]);

        // Hitung nilai akhir dengan bobot
        $finalGrade = $weightConfig->calculateFinalGrade(
            $uhAverage,
            $utsScore,
            $uasScore,
            $praktikAverage
        );

        return [
            'final_grade' => $finalGrade,
            'predikat' => Grade::getPredikatFromScore($finalGrade),
            'predikat_label' => Grade::PREDIKAT_RANGES[Grade::getPredikatFromScore($finalGrade)]['label'],
            'breakdown' => [
                'uh' => [
                    'average' => $uhAverage,
                    'weight' => $weightConfig->uh_weight,
                    'weighted' => round($uhAverage * $weightConfig->uh_weight / 100, 2),
                    'count' => $grades->where('assessment_type', Grade::ASSESSMENT_UH)->count(),
                ],
                'uts' => [
                    'score' => $utsScore,
                    'weight' => $weightConfig->uts_weight,
                    'weighted' => round($utsScore * $weightConfig->uts_weight / 100, 2),
                ],
                'uas' => [
                    'score' => $uasScore,
                    'weight' => $weightConfig->uas_weight,
                    'weighted' => round($uasScore * $weightConfig->uas_weight / 100, 2),
                ],
                'praktik' => [
                    'average' => $praktikAverage,
                    'weight' => $weightConfig->praktik_weight,
                    'weighted' => round($praktikAverage * $weightConfig->praktik_weight / 100, 2),
                    'count' => $grades->whereIn('assessment_type', [
                        Grade::ASSESSMENT_PRAKTIK,
                        Grade::ASSESSMENT_PROYEK,
                    ])->count(),
                ],
            ],
        ];
    }

    /**
     * Menghitung rata-rata nilai kelas untuk suatu mata pelajaran
     *
     * @return array{average: float, predikat: string, student_count: int, grade_distribution: array}
     */
    public function calculateClassAverage(
        int $classId,
        int $subjectId,
        string $tahunAjaran,
        string $semester
    ): array {
        $class = SchoolClass::with('students')->findOrFail($classId);
        $studentIds = $class->students->pluck('id')->toArray();

        if (empty($studentIds)) {
            return [
                'average' => 0,
                'predikat' => 'D',
                'student_count' => 0,
                'grade_distribution' => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0],
            ];
        }

        $studentGrades = [];
        $distribution = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0];

        foreach ($studentIds as $studentId) {
            $result = $this->calculateFinalGrade($studentId, $subjectId, $tahunAjaran, $semester);
            $studentGrades[] = $result['final_grade'];
            $distribution[$result['predikat']]++;
        }

        $average = count($studentGrades) > 0
            ? round(array_sum($studentGrades) / count($studentGrades), 2)
            : 0;

        return [
            'average' => $average,
            'predikat' => Grade::getPredikatFromScore($average),
            'student_count' => count($studentIds),
            'grade_distribution' => $distribution,
        ];
    }

    /**
     * Menghitung ranking siswa dalam kelas berdasarkan nilai rata-rata semua mapel
     *
     * @return Collection<int, array{student_id: int, student: Student, average: float, rank: int}>
     */
    public function calculateRanking(
        int $classId,
        string $tahunAjaran,
        string $semester
    ): Collection {
        $class = SchoolClass::with(['students', 'subjects'])->findOrFail($classId);
        $students = $class->students;
        $subjectIds = $class->subjects->pluck('id')->unique()->toArray();

        if ($students->isEmpty() || empty($subjectIds)) {
            return collect();
        }

        $studentAverages = [];

        foreach ($students as $student) {
            $subjectGrades = [];

            foreach ($subjectIds as $subjectId) {
                $result = $this->calculateFinalGrade(
                    $student->id,
                    $subjectId,
                    $tahunAjaran,
                    $semester
                );
                $subjectGrades[] = $result['final_grade'];
            }

            $overallAverage = count($subjectGrades) > 0
                ? round(array_sum($subjectGrades) / count($subjectGrades), 2)
                : 0;

            $studentAverages[] = [
                'student_id' => $student->id,
                'student' => $student,
                'average' => $overallAverage,
                'rank' => 0,
            ];
        }

        // Sort by average descending dan assign rank
        $sorted = collect($studentAverages)->sortByDesc('average')->values();

        $currentRank = 0;
        $lastAverage = null;
        $skipCount = 0;

        return $sorted->map(function ($item, $index) use (&$currentRank, &$lastAverage, &$skipCount) {
            if ($lastAverage !== $item['average']) {
                $currentRank = $index + 1 + $skipCount;
                $skipCount = 0;
            } else {
                $skipCount++;
            }

            $lastAverage = $item['average'];
            $item['rank'] = $currentRank;

            return $item;
        });
    }

    /**
     * Mendapatkan rekap nilai lengkap untuk satu siswa per semester
     *
     * @return array{subjects: array, overall_average: float, rank: int|null}
     */
    public function getStudentGradeSummary(
        int $studentId,
        string $tahunAjaran,
        string $semester
    ): array {
        $student = Student::with('kelas.subjects')->findOrFail($studentId);
        $class = $student->kelas;

        if (! $class) {
            return [
                'subjects' => [],
                'overall_average' => 0,
                'rank' => null,
            ];
        }

        $subjectIds = $class->subjects->pluck('id')->unique()->toArray();
        $subjectResults = [];
        $allFinalGrades = [];

        foreach ($subjectIds as $subjectId) {
            $subject = Subject::find($subjectId);
            $result = $this->calculateFinalGrade($studentId, $subjectId, $tahunAjaran, $semester);

            $subjectResults[] = [
                'subject_id' => $subjectId,
                'subject_name' => $subject->nama_mapel ?? 'Unknown',
                'subject_code' => $subject->kode_mapel ?? '',
                'final_grade' => $result['final_grade'],
                'predikat' => $result['predikat'],
                'predikat_label' => $result['predikat_label'],
                'breakdown' => $result['breakdown'],
            ];

            $allFinalGrades[] = $result['final_grade'];
        }

        $overallAverage = count($allFinalGrades) > 0
            ? round(array_sum($allFinalGrades) / count($allFinalGrades), 2)
            : 0;

        // Hitung ranking
        $rankings = $this->calculateRanking($class->id, $tahunAjaran, $semester);
        $studentRanking = $rankings->firstWhere('student_id', $studentId);

        return [
            'subjects' => $subjectResults,
            'overall_average' => $overallAverage,
            'rank' => $studentRanking['rank'] ?? null,
            'total_students' => $rankings->count(),
        ];
    }

    /**
     * Mendapatkan rekap nilai per kelas dengan semua siswa dan mapel
     *
     * @return array{class: SchoolClass, students: array, subjects: array, statistics: array}
     */
    public function getClassGradeSummary(
        int $classId,
        string $tahunAjaran,
        string $semester
    ): array {
        $class = SchoolClass::with(['students', 'subjects', 'waliKelas'])->findOrFail($classId);
        $students = $class->students;
        $subjects = $class->subjects->unique('id');

        $studentsData = [];
        $subjectsData = [];

        // Prepare subjects data
        foreach ($subjects as $subject) {
            $subjectsData[$subject->id] = [
                'id' => $subject->id,
                'code' => $subject->kode_mapel,
                'name' => $subject->nama_mapel,
            ];
        }

        // Calculate grades for each student
        foreach ($students as $student) {
            $summary = $this->getStudentGradeSummary($student->id, $tahunAjaran, $semester);

            $studentsData[] = [
                'id' => $student->id,
                'nis' => $student->nis,
                'name' => $student->nama_lengkap,
                'subjects' => collect($summary['subjects'])->keyBy('subject_id')->toArray(),
                'overall_average' => $summary['overall_average'],
                'rank' => $summary['rank'],
            ];
        }

        // Sort by rank
        usort($studentsData, fn ($a, $b) => ($a['rank'] ?? 999) <=> ($b['rank'] ?? 999));

        // Calculate class statistics per subject
        $statistics = [];
        foreach ($subjects as $subject) {
            $classAvg = $this->calculateClassAverage($classId, $subject->id, $tahunAjaran, $semester);
            $statistics[$subject->id] = $classAvg;
        }

        return [
            'class' => [
                'id' => $class->id,
                'name' => $class->nama_lengkap,
                'wali_kelas' => $class->waliKelas?->name ?? null,
            ],
            'students' => $studentsData,
            'subjects' => array_values($subjectsData),
            'statistics' => $statistics,
        ];
    }

    /**
     * Helper: Menghitung rata-rata nilai untuk komponen tertentu
     *
     * @param  Collection<int, Grade>  $grades
     */
    protected function calculateComponentAverage(Collection $grades, string|array $assessmentTypes): float
    {
        $types = is_array($assessmentTypes) ? $assessmentTypes : [$assessmentTypes];

        $filtered = $grades->whereIn('assessment_type', $types);

        if ($filtered->isEmpty()) {
            return 0;
        }

        return round($filtered->avg('score'), 2);
    }

    /**
     * Helper: Mendapatkan nilai terakhir untuk komponen (UTS/UAS)
     *
     * @param  Collection<int, Grade>  $grades
     */
    protected function getLatestScore(Collection $grades, string $assessmentType): float
    {
        $grade = $grades
            ->where('assessment_type', $assessmentType)
            ->sortByDesc('assessment_date')
            ->first();

        return $grade?->score ?? 0;
    }

    /**
     * Static helper untuk mendapatkan predikat dari score
     */
    public static function getPredikat(float $score): string
    {
        return Grade::getPredikatFromScore($score);
    }

    /**
     * Static helper untuk mendapatkan label predikat
     */
    public static function getPredikatLabel(float $score): string
    {
        $predikat = Grade::getPredikatFromScore($score);

        return Grade::PREDIKAT_RANGES[$predikat]['label'] ?? 'Tidak Diketahui';
    }
}
