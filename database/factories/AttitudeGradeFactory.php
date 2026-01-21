<?php

namespace Database\Factories;

use App\Models\AttitudeGrade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttitudeGrade>
 */
class AttitudeGradeFactory extends Factory
{
    protected $model = AttitudeGrade::class;

    /**
     * Define the model's default state untuk generate realistic attitude grade data
     * yang digunakan untuk testing dan seeding
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $grades = ['A', 'B', 'C', 'D'];
        $spiritualGrade = fake()->randomElement($grades);
        $socialGrade = fake()->randomElement($grades);

        return [
            'student_id' => Student::factory(),
            'class_id' => SchoolClass::factory(),
            'teacher_id' => User::factory()->state(['role' => 'TEACHER']),
            'tahun_ajaran' => '2024/2025',
            'semester' => fake()->randomElement(['1', '2']),
            'spiritual_grade' => $spiritualGrade,
            'spiritual_description' => $this->getSpiritualDescription($spiritualGrade),
            'social_grade' => $socialGrade,
            'social_description' => $this->getSocialDescription($socialGrade),
            'homeroom_notes' => fake()->optional(0.7)->paragraph(),
        ];
    }

    /**
     * Generate deskripsi sikap spiritual berdasarkan predikat
     */
    private function getSpiritualDescription(string $grade): string
    {
        return match ($grade) {
            'A' => 'Siswa selalu menunjukkan perilaku beriman dan bertakwa kepada Tuhan Yang Maha Esa dengan sangat baik.',
            'B' => 'Siswa menunjukkan perilaku beriman dan bertakwa kepada Tuhan Yang Maha Esa dengan baik.',
            'C' => 'Siswa cukup menunjukkan perilaku beriman dan bertakwa kepada Tuhan Yang Maha Esa.',
            'D' => 'Siswa perlu bimbingan lebih lanjut dalam menunjukkan perilaku beriman dan bertakwa.',
            default => '',
        };
    }

    /**
     * Generate deskripsi sikap sosial berdasarkan predikat
     */
    private function getSocialDescription(string $grade): string
    {
        return match ($grade) {
            'A' => 'Siswa selalu menunjukkan perilaku jujur, disiplin, tanggung jawab, santun, peduli, dan percaya diri dengan sangat baik.',
            'B' => 'Siswa menunjukkan perilaku jujur, disiplin, tanggung jawab, santun, peduli, dan percaya diri dengan baik.',
            'C' => 'Siswa cukup menunjukkan perilaku jujur, disiplin, tanggung jawab, santun, peduli, dan percaya diri.',
            'D' => 'Siswa perlu bimbingan lebih lanjut dalam perilaku jujur, disiplin, tanggung jawab, santun, peduli, dan percaya diri.',
            default => '',
        };
    }

    /**
     * State untuk nilai sikap sangat baik (A)
     */
    public function excellent(): static
    {
        return $this->state(fn (array $attributes) => [
            'spiritual_grade' => 'A',
            'spiritual_description' => $this->getSpiritualDescription('A'),
            'social_grade' => 'A',
            'social_description' => $this->getSocialDescription('A'),
        ]);
    }

    /**
     * State untuk nilai sikap baik (B)
     */
    public function good(): static
    {
        return $this->state(fn (array $attributes) => [
            'spiritual_grade' => 'B',
            'spiritual_description' => $this->getSpiritualDescription('B'),
            'social_grade' => 'B',
            'social_description' => $this->getSocialDescription('B'),
        ]);
    }

    /**
     * State untuk nilai sikap cukup (C)
     */
    public function adequate(): static
    {
        return $this->state(fn (array $attributes) => [
            'spiritual_grade' => 'C',
            'spiritual_description' => $this->getSpiritualDescription('C'),
            'social_grade' => 'C',
            'social_description' => $this->getSocialDescription('C'),
        ]);
    }

    /**
     * State untuk nilai sikap kurang (D)
     */
    public function poor(): static
    {
        return $this->state(fn (array $attributes) => [
            'spiritual_grade' => 'D',
            'spiritual_description' => $this->getSpiritualDescription('D'),
            'social_grade' => 'D',
            'social_description' => $this->getSocialDescription('D'),
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
     * State dengan catatan wali kelas
     */
    public function withHomeroomNotes(?string $notes = null): static
    {
        return $this->state(fn (array $attributes) => [
            'homeroom_notes' => $notes ?? fake()->paragraph(),
        ]);
    }
}
