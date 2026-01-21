<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\GradeWeightConfig;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class GradesModuleSeeder extends Seeder
{
    /**
     * Seeder untuk modul Grades & Report Cards yang men-generate
     * data sample untuk testing dan development
     */
    public function run(): void
    {
        $tahunAjaran = '2024/2025';

        // 1. Create default grade weight config
        $this->seedGradeWeightConfig($tahunAjaran);

        // 2. Seed sample grades untuk siswa yang ada
        $this->seedSampleGrades($tahunAjaran);
    }

    /**
     * Seed konfigurasi bobot nilai default
     */
    private function seedGradeWeightConfig(string $tahunAjaran): void
    {
        // Default config untuk semua mapel
        GradeWeightConfig::firstOrCreate(
            [
                'tahun_ajaran' => $tahunAjaran,
                'is_default' => true,
            ],
            [
                'subject_id' => null,
                'uh_weight' => 30,
                'uts_weight' => 25,
                'uas_weight' => 30,
                'praktik_weight' => 15,
            ]
        );

        $this->command->info('Grade weight config seeded.');
    }

    /**
     * Seed sample grades untuk siswa yang sudah ada
     */
    private function seedSampleGrades(string $tahunAjaran): void
    {
        // Ambil beberapa kelas aktif
        $classes = SchoolClass::active()
            ->with(['students', 'waliKelas'])
            ->take(3)
            ->get();

        if ($classes->isEmpty()) {
            $this->command->warn('No active classes found. Skipping grades seeding.');

            return;
        }

        // Ambil semua subjects aktif
        $subjects = Subject::active()->get();

        if ($subjects->isEmpty()) {
            $this->command->warn('No active subjects found. Skipping grades seeding.');

            return;
        }

        // Ambil teachers
        $teachers = User::teachers()->active()->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('No active teachers found. Skipping grades seeding.');

            return;
        }

        foreach ($classes as $class) {
            $students = $class->students()->active()->get();

            if ($students->isEmpty()) {
                continue;
            }

            // Untuk setiap subject, buat grades untuk semua siswa
            foreach ($subjects->take(5) as $subject) {
                $teacher = $teachers->random();

                // Generate UH 1, UH 2, UH 3
                foreach (range(1, 3) as $uhNumber) {
                    $this->createGradesForStudents(
                        $students,
                        $subject,
                        $class,
                        $teacher,
                        $tahunAjaran,
                        '1',
                        'UH',
                        $uhNumber,
                        "Ulangan Harian {$uhNumber}"
                    );
                }

                // Generate UTS
                $this->createGradesForStudents(
                    $students,
                    $subject,
                    $class,
                    $teacher,
                    $tahunAjaran,
                    '1',
                    'UTS',
                    null,
                    'Ujian Tengah Semester'
                );

                // Generate UAS
                $this->createGradesForStudents(
                    $students,
                    $subject,
                    $class,
                    $teacher,
                    $tahunAjaran,
                    '1',
                    'UAS',
                    null,
                    'Ujian Akhir Semester'
                );
            }

            $this->command->info("Grades seeded for class {$class->nama_lengkap}");
        }
    }

    /**
     * Helper untuk membuat grades untuk semua siswa dalam satu assessment
     */
    private function createGradesForStudents(
        $students,
        Subject $subject,
        SchoolClass $class,
        User $teacher,
        string $tahunAjaran,
        string $semester,
        string $assessmentType,
        ?int $assessmentNumber,
        string $title
    ): void {
        foreach ($students as $student) {
            Grade::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'class_id' => $class->id,
                    'tahun_ajaran' => $tahunAjaran,
                    'semester' => $semester,
                    'assessment_type' => $assessmentType,
                    'assessment_number' => $assessmentNumber,
                ],
                [
                    'teacher_id' => $teacher->id,
                    'title' => $title,
                    'assessment_date' => now()->subDays(rand(1, 90)),
                    'score' => $this->generateRealisticScore(),
                    'notes' => null,
                    'is_locked' => false,
                ]
            );
        }
    }

    /**
     * Generate skor realistis dengan distribusi normal
     * Mayoritas siswa mendapat nilai 70-90
     */
    private function generateRealisticScore(): float
    {
        // Distribusi: 10% dapat 90-100, 50% dapat 75-89, 30% dapat 60-74, 10% dapat <60
        $rand = rand(1, 100);

        if ($rand <= 10) {
            return round(rand(9000, 10000) / 100, 2); // 90-100
        } elseif ($rand <= 60) {
            return round(rand(7500, 8999) / 100, 2); // 75-89.99
        } elseif ($rand <= 90) {
            return round(rand(6000, 7499) / 100, 2); // 60-74.99
        } else {
            return round(rand(4000, 5999) / 100, 2); // 40-59.99
        }
    }
}
