<?php

namespace App\Services;

use App\Models\AttitudeGrade;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * ReportCardService - Service untuk mengelola generate rapor siswa
 * termasuk validasi kelengkapan data, generate PDF menggunakan dompdf,
 * dan lock/unlock nilai setelah finalisasi rapor
 */
class ReportCardService
{
    /**
     * Constructor untuk inject dependency GradeCalculationService
     */
    public function __construct(
        protected GradeCalculationService $gradeCalculationService
    ) {}

    /**
     * Validasi kelengkapan data nilai untuk satu kelas per semester
     * Mengecek apakah semua siswa memiliki nilai dan sikap yang lengkap
     *
     * @return array{is_complete: bool, students: array, missing_count: int, complete_count: int}
     */
    public function validateCompleteness(
        int $classId,
        string $tahunAjaran,
        string $semester
    ): array {
        $class = SchoolClass::with(['students', 'subjects'])->findOrFail($classId);
        $students = $class->students()->where('status', 'aktif')->get();
        $subjects = $class->subjects->unique('id');

        $studentsData = [];
        $completeCount = 0;
        $missingCount = 0;

        foreach ($students as $student) {
            $studentValidation = $this->validateStudentCompleteness(
                $student,
                $subjects,
                $tahunAjaran,
                $semester
            );

            $studentsData[] = $studentValidation;

            if ($studentValidation['is_complete']) {
                $completeCount++;
            } else {
                $missingCount++;
            }
        }

        return [
            'is_complete' => $missingCount === 0,
            'students' => $studentsData,
            'missing_count' => $missingCount,
            'complete_count' => $completeCount,
            'total_students' => count($students),
        ];
    }

    /**
     * Validasi kelengkapan data nilai untuk satu siswa
     * Mengecek nilai per mapel dan nilai sikap
     *
     * @param  Collection<int, \App\Models\Subject>  $subjects
     * @return array{student_id: int, student_name: string, nis: string, is_complete: bool, missing: array}
     */
    protected function validateStudentCompleteness(
        Student $student,
        Collection $subjects,
        string $tahunAjaran,
        string $semester
    ): array {
        $missing = [];

        // Check nilai per mata pelajaran dengan minimal UH, UTS, UAS
        foreach ($subjects as $subject) {
            $grades = Grade::query()
                ->byStudent($student->id)
                ->bySubject($subject->id)
                ->byTahunAjaran($tahunAjaran)
                ->bySemester($semester)
                ->get();

            $hasUH = $grades->where('assessment_type', Grade::ASSESSMENT_UH)->count() > 0;
            $hasUTS = $grades->where('assessment_type', Grade::ASSESSMENT_UTS)->count() > 0;
            $hasUAS = $grades->where('assessment_type', Grade::ASSESSMENT_UAS)->count() > 0;

            if (! $hasUH) {
                $missing[] = "UH {$subject->nama_mapel} belum diinput";
            }
            if (! $hasUTS) {
                $missing[] = "UTS {$subject->nama_mapel} belum diinput";
            }
            if (! $hasUAS) {
                $missing[] = "UAS {$subject->nama_mapel} belum diinput";
            }
        }

        // Check nilai sikap
        $attitudeGrade = AttitudeGrade::query()
            ->byStudent($student->id)
            ->byTahunAjaran($tahunAjaran)
            ->bySemester($semester)
            ->first();

        if (! $attitudeGrade) {
            $missing[] = 'Nilai sikap belum diinput';
        }

        return [
            'student_id' => $student->id,
            'student_name' => $student->nama_lengkap,
            'nis' => $student->nis,
            'is_complete' => count($missing) === 0,
            'missing' => $missing,
        ];
    }

    /**
     * Generate report card untuk satu siswa
     * Membuat record ReportCard dan generate PDF
     *
     * @return array{success: bool, report_card: ReportCard|null, error: string|null}
     */
    public function generateReportCard(
        int $studentId,
        int $classId,
        string $tahunAjaran,
        string $semester,
        int $generatedBy
    ): array {
        try {
            $student = Student::with('kelas')->findOrFail($studentId);
            $class = SchoolClass::with(['waliKelas', 'subjects'])->findOrFail($classId);

            // Get existing or create new report card
            $reportCard = ReportCard::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'class_id' => $classId,
                    'tahun_ajaran' => $tahunAjaran,
                    'semester' => $semester,
                ],
                [
                    'status' => ReportCard::STATUS_DRAFT,
                    'generated_at' => now(),
                    'generated_by' => $generatedBy,
                ]
            );

            // Calculate grades summary
            $gradeSummary = $this->gradeCalculationService->getStudentGradeSummary(
                $studentId,
                $tahunAjaran,
                $semester
            );

            // Update report card dengan average dan rank
            $reportCard->update([
                'average_score' => $gradeSummary['overall_average'],
                'rank' => $gradeSummary['rank'],
            ]);

            // Generate PDF
            $pdfPath = $this->generatePDF($reportCard);
            $reportCard->update(['pdf_path' => $pdfPath]);

            return [
                'success' => true,
                'report_card' => $reportCard->fresh(),
                'error' => null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'report_card' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate report cards untuk semua siswa dalam satu kelas (bulk)
     *
     * @return array{success: bool, generated_count: int, failed_count: int, results: array}
     */
    public function generateBulk(
        int $classId,
        string $tahunAjaran,
        string $semester,
        int $generatedBy
    ): array {
        $class = SchoolClass::with('students')->findOrFail($classId);
        $students = $class->students()->where('status', 'aktif')->get();

        $results = [];
        $generatedCount = 0;
        $failedCount = 0;

        DB::beginTransaction();
        try {
            // Lock all grades for this class first
            $this->lockGrades($classId, $tahunAjaran, $semester, $generatedBy);

            foreach ($students as $student) {
                $result = $this->generateReportCard(
                    $student->id,
                    $classId,
                    $tahunAjaran,
                    $semester,
                    $generatedBy
                );

                $results[] = [
                    'student_id' => $student->id,
                    'student_name' => $student->nama_lengkap,
                    'success' => $result['success'],
                    'error' => $result['error'],
                ];

                if ($result['success']) {
                    $generatedCount++;
                } else {
                    $failedCount++;
                }
            }

            DB::commit();

            return [
                'success' => $failedCount === 0,
                'generated_count' => $generatedCount,
                'failed_count' => $failedCount,
                'results' => $results,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'generated_count' => 0,
                'failed_count' => count($students),
                'results' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate PDF rapor menggunakan dompdf
     * Menyimpan PDF ke storage dan return path
     */
    public function generatePDF(ReportCard $reportCard): string
    {
        $data = $this->getReportCardData($reportCard);

        $pdf = Pdf::loadView('pdf.report-card', $data);
        $pdf->setPaper('a4', 'portrait');

        // Create storage path
        $path = "report-cards/{$reportCard->tahun_ajaran}/{$reportCard->semester}/{$reportCard->class_id}";
        $filename = "{$reportCard->student->nis}_{$reportCard->student->nama_lengkap}.pdf";
        $filename = str_replace(['/', '\\', ' '], ['_', '_', '_'], $filename);

        $fullPath = "{$path}/{$filename}";

        // Save to storage
        Storage::put($fullPath, $pdf->output());

        return $fullPath;
    }

    /**
     * Get data lengkap untuk generate PDF rapor
     * Termasuk data siswa, nilai per mapel, sikap, dan kehadiran
     *
     * @return array<string, mixed>
     */
    public function getReportCardData(ReportCard $reportCard): array
    {
        $student = $reportCard->student;
        $class = $reportCard->class;

        // Get grade summary
        $gradeSummary = $this->gradeCalculationService->getStudentGradeSummary(
            $reportCard->student_id,
            $reportCard->tahun_ajaran,
            $reportCard->semester
        );

        // Get attitude grade
        $attitudeGrade = AttitudeGrade::query()
            ->byStudent($reportCard->student_id)
            ->byTahunAjaran($reportCard->tahun_ajaran)
            ->bySemester($reportCard->semester)
            ->first();

        // Get attendance summary dengan perhitungan berdasarkan semester
        $attendanceSummary = $this->getAttendanceSummary(
            $reportCard->student_id,
            $reportCard->tahun_ajaran,
            $reportCard->semester
        );

        return [
            'report_card' => $reportCard,
            'student' => [
                'nama_lengkap' => $student->nama_lengkap,
                'nis' => $student->nis,
                'nisn' => $student->nisn ?? '-',
                'jenis_kelamin' => $student->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                'tempat_lahir' => $student->tempat_lahir ?? '-',
                'tanggal_lahir' => $student->tanggal_lahir?->format('d F Y') ?? '-',
                'agama' => $student->agama ?? '-',
                'alamat' => $student->alamat ?? '-',
                'nama_ayah' => $student->nama_ayah ?? '-',
                'nama_ibu' => $student->nama_ibu ?? '-',
            ],
            'class' => [
                'nama_lengkap' => $class->nama_lengkap,
                'tingkat' => $class->tingkat,
                'wali_kelas' => $class->waliKelas?->name ?? '-',
            ],
            'academic' => [
                'tahun_ajaran' => $reportCard->tahun_ajaran,
                'semester' => $reportCard->semester,
                'semester_label' => $reportCard->semester === '1' ? 'Ganjil' : 'Genap',
            ],
            'grades' => $gradeSummary['subjects'],
            'overall' => [
                'average' => $gradeSummary['overall_average'],
                'rank' => $gradeSummary['rank'],
                'total_students' => $gradeSummary['total_students'],
            ],
            'attitude' => $attitudeGrade ? [
                'spiritual_grade' => $attitudeGrade->spiritual_grade,
                'spiritual_label' => $attitudeGrade->spiritual_grade_label,
                'spiritual_description' => $attitudeGrade->spiritual_description ?? AttitudeGrade::getSpiritualDescriptionTemplates()[$attitudeGrade->spiritual_grade] ?? '-',
                'social_grade' => $attitudeGrade->social_grade,
                'social_label' => $attitudeGrade->social_grade_label,
                'social_description' => $attitudeGrade->social_description ?? AttitudeGrade::getSocialDescriptionTemplates()[$attitudeGrade->social_grade] ?? '-',
                'homeroom_notes' => $attitudeGrade->homeroom_notes ?? '',
            ] : null,
            'attendance' => $attendanceSummary,
            'generated_at' => now()->format('d F Y'),
        ];
    }

    /**
     * Get summary kehadiran siswa per semester
     * dengan format: Hadir, Sakit, Izin, Alpha
     *
     * @return array{hadir: int, sakit: int, izin: int, alpha: int, total_days: int}
     */
    protected function getAttendanceSummary(
        int $studentId,
        string $tahunAjaran,
        string $semester
    ): array {
        // Determine date range based on semester dan tahun ajaran
        $years = explode('/', $tahunAjaran);
        $startYear = (int) $years[0];
        $endYear = (int) ($years[1] ?? $startYear + 1);

        if ($semester === '1') {
            // Semester 1: Juli - Desember
            $startDate = "{$startYear}-07-01";
            $endDate = "{$startYear}-12-31";
        } else {
            // Semester 2: Januari - Juni
            $startDate = "{$endYear}-01-01";
            $endDate = "{$endYear}-06-30";
        }

        $attendances = StudentAttendance::where('student_id', $studentId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        return [
            'hadir' => $attendances->where('status', 'H')->count(),
            'sakit' => $attendances->where('status', 'S')->count(),
            'izin' => $attendances->where('status', 'I')->count(),
            'alpha' => $attendances->where('status', 'A')->count(),
            'total_days' => $attendances->count(),
        ];
    }

    /**
     * Lock semua nilai untuk kelas tertentu per semester
     * Setelah di-lock, guru tidak dapat mengubah nilai
     */
    public function lockGrades(
        int $classId,
        string $tahunAjaran,
        string $semester,
        int $lockedBy
    ): int {
        return Grade::query()
            ->byClass($classId)
            ->byTahunAjaran($tahunAjaran)
            ->bySemester($semester)
            ->unlocked()
            ->update([
                'is_locked' => true,
                'locked_at' => now(),
                'locked_by' => $lockedBy,
            ]);
    }

    /**
     * Unlock semua nilai untuk kelas tertentu per semester
     * Hanya admin yang dapat melakukan unlock
     */
    public function unlockGrades(
        int $classId,
        string $tahunAjaran,
        string $semester
    ): int {
        return Grade::query()
            ->byClass($classId)
            ->byTahunAjaran($tahunAjaran)
            ->bySemester($semester)
            ->locked()
            ->update([
                'is_locked' => false,
                'locked_at' => null,
                'locked_by' => null,
            ]);
    }

    /**
     * Unlock single report card dan nilai terkait
     * Untuk koreksi nilai setelah rapor di-generate
     */
    public function unlockReportCard(ReportCard $reportCard): bool
    {
        if ($reportCard->status !== ReportCard::STATUS_DRAFT) {
            return false;
        }

        // Unlock grades for this student
        Grade::query()
            ->byStudent($reportCard->student_id)
            ->byClass($reportCard->class_id)
            ->byTahunAjaran($reportCard->tahun_ajaran)
            ->bySemester($reportCard->semester)
            ->update([
                'is_locked' => false,
                'locked_at' => null,
                'locked_by' => null,
            ]);

        // Delete PDF if exists
        if ($reportCard->pdf_path && Storage::exists($reportCard->pdf_path)) {
            Storage::delete($reportCard->pdf_path);
        }

        // Reset report card
        return $reportCard->update([
            'pdf_path' => null,
            'generated_at' => null,
        ]);
    }

    /**
     * Download PDF rapor dari storage
     */
    public function downloadPDF(ReportCard $reportCard): ?string
    {
        if (! $reportCard->pdf_path || ! Storage::exists($reportCard->pdf_path)) {
            return null;
        }

        return Storage::path($reportCard->pdf_path);
    }

    /**
     * Create ZIP file containing all PDFs for a class
     *
     * @return string|null Path to ZIP file
     */
    public function createBulkZip(
        int $classId,
        string $tahunAjaran,
        string $semester
    ): ?string {
        $reportCards = ReportCard::query()
            ->byClass($classId)
            ->byTahunAjaran($tahunAjaran)
            ->bySemester($semester)
            ->whereNotNull('pdf_path')
            ->get();

        if ($reportCards->isEmpty()) {
            return null;
        }

        $class = SchoolClass::find($classId);
        $zipFileName = "rapor_{$class->nama_lengkap}_{$tahunAjaran}_{$semester}.zip";
        $zipPath = "report-cards/zip/{$zipFileName}";

        $zip = new \ZipArchive;
        $tempZipPath = storage_path("app/{$zipPath}");

        // Ensure directory exists
        $zipDir = dirname($tempZipPath);
        if (! is_dir($zipDir)) {
            mkdir($zipDir, 0755, true);
        }

        if ($zip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($reportCards as $reportCard) {
                if (Storage::exists($reportCard->pdf_path)) {
                    $pdfContent = Storage::get($reportCard->pdf_path);
                    $filename = basename($reportCard->pdf_path);
                    $zip->addFromString($filename, $pdfContent);
                }
            }
            $zip->close();

            return $zipPath;
        }

        return null;
    }

    /**
     * Get current tahun ajaran berdasarkan bulan sekarang
     */
    public function getCurrentTahunAjaran(): string
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
     * Get current semester berdasarkan bulan sekarang
     * Semester 1: Juli - Desember
     * Semester 2: Januari - Juni
     */
    public function getCurrentSemester(): string
    {
        $month = now()->month;

        return $month >= 7 ? '1' : '2';
    }

    /**
     * Get list tahun ajaran yang tersedia
     *
     * @return array<string>
     */
    public function getAvailableTahunAjaran(): array
    {
        return ReportCard::query()
            ->distinct()
            ->pluck('tahun_ajaran')
            ->merge(Grade::query()->distinct()->pluck('tahun_ajaran'))
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }
}
