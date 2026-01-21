<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grade>
 */
class GradeFactory extends Factory
{
    protected $model = Grade::class;

    /**
     * Define the model's default state untuk generate realistic grade data
     * yang digunakan untuk testing dan seeding
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $assessmentTypes = ['UH', 'UTS', 'UAS', 'PRAKTIK', 'PROYEK'];
        $assessmentType = fake()->randomElement($assessmentTypes);

        // Untuk UH, beri nomor urut 1-5
        $assessmentNumber = $assessmentType === 'UH' ? fake()->numberBetween(1, 5) : null;

        // Generate title berdasarkan jenis assessment
        $title = $this->generateTitle($assessmentType, $assessmentNumber);

        return [
            'student_id' => Student::factory(),
            'subject_id' => Subject::factory(),
            'class_id' => SchoolClass::factory(),
            'teacher_id' => User::factory()->state(['role' => 'TEACHER']),
            'tahun_ajaran' => '2024/2025',
            'semester' => fake()->randomElement(['1', '2']),
            'assessment_type' => $assessmentType,
            'assessment_number' => $assessmentNumber,
            'title' => $title,
            'assessment_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'score' => fake()->randomFloat(2, 50, 100),
            'notes' => fake()->optional(0.3)->sentence(),
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
        ];
    }

    /**
     * Generate title berdasarkan jenis assessment
     */
    private function generateTitle(string $type, ?int $number): string
    {
        return match ($type) {
            'UH' => "Ulangan Harian {$number}",
            'UTS' => 'Ujian Tengah Semester',
            'UAS' => 'Ujian Akhir Semester',
            'PRAKTIK' => 'Praktik '.fake()->randomElement(['1', '2', '3']),
            'PROYEK' => 'Proyek '.fake()->word(),
            default => $type,
        };
    }

    /**
     * State untuk grade dengan jenis UH (Ulangan Harian)
     */
    public function ulanganHarian(int $number = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'assessment_type' => 'UH',
            'assessment_number' => $number,
            'title' => "Ulangan Harian {$number}",
        ]);
    }

    /**
     * State untuk grade dengan jenis UTS
     */
    public function uts(): static
    {
        return $this->state(fn (array $attributes) => [
            'assessment_type' => 'UTS',
            'assessment_number' => null,
            'title' => 'Ujian Tengah Semester',
        ]);
    }

    /**
     * State untuk grade dengan jenis UAS
     */
    public function uas(): static
    {
        return $this->state(fn (array $attributes) => [
            'assessment_type' => 'UAS',
            'assessment_number' => null,
            'title' => 'Ujian Akhir Semester',
        ]);
    }

    /**
     * State untuk grade dengan jenis Praktik
     */
    public function praktik(): static
    {
        return $this->state(fn (array $attributes) => [
            'assessment_type' => 'PRAKTIK',
            'assessment_number' => null,
            'title' => 'Praktik',
        ]);
    }

    /**
     * State untuk grade yang sudah dikunci
     */
    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => User::factory()->state(['role' => 'ADMIN']),
        ]);
    }

    /**
     * State untuk semester 1
     */
    public function semester1(): static
    {
        return $this->state(fn (array $attributes) => [
            'semester' => '1',
        ]);
    }

    /**
     * State untuk semester 2
     */
    public function semester2(): static
    {
        return $this->state(fn (array $attributes) => [
            'semester' => '2',
        ]);
    }

    /**
     * State untuk tahun ajaran tertentu
     */
    public function forTahunAjaran(string $tahunAjaran): static
    {
        return $this->state(fn (array $attributes) => [
            'tahun_ajaran' => $tahunAjaran,
        ]);
    }

    /**
     * State untuk nilai tinggi (A)
     */
    public function highScore(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->randomFloat(2, 90, 100),
        ]);
    }

    /**
     * State untuk nilai rendah (D)
     */
    public function lowScore(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->randomFloat(2, 0, 69),
        ]);
    }
}
