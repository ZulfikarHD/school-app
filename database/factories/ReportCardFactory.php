<?php

namespace Database\Factories;

use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReportCard>
 */
class ReportCardFactory extends Factory
{
    protected $model = ReportCard::class;

    /**
     * Define the model's default state untuk generate realistic report card data
     * yang digunakan untuk testing dan seeding
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'class_id' => SchoolClass::factory(),
            'tahun_ajaran' => '2024/2025',
            'semester' => fake()->randomElement(['1', '2']),
            'status' => ReportCard::STATUS_DRAFT,
            'generated_at' => null,
            'generated_by' => null,
            'approved_at' => null,
            'approved_by' => null,
            'approval_notes' => null,
            'released_at' => null,
            'pdf_path' => null,
            'average_score' => null,
            'rank' => null,
        ];
    }

    /**
     * State untuk rapor dengan status draft
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportCard::STATUS_DRAFT,
            'generated_at' => null,
            'generated_by' => null,
        ]);
    }

    /**
     * State untuk rapor yang menunggu approval
     */
    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportCard::STATUS_PENDING_APPROVAL,
            'generated_at' => now()->subDays(fake()->numberBetween(1, 7)),
            'generated_by' => User::factory()->state(['role' => 'ADMIN']),
            'average_score' => fake()->randomFloat(2, 70, 95),
            'rank' => fake()->numberBetween(1, 30),
        ]);
    }

    /**
     * State untuk rapor yang sudah disetujui
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportCard::STATUS_APPROVED,
            'generated_at' => now()->subDays(fake()->numberBetween(7, 14)),
            'generated_by' => User::factory()->state(['role' => 'ADMIN']),
            'approved_at' => now()->subDays(fake()->numberBetween(1, 7)),
            'approved_by' => User::factory()->state(['role' => 'PRINCIPAL']),
            'average_score' => fake()->randomFloat(2, 70, 95),
            'rank' => fake()->numberBetween(1, 30),
        ]);
    }

    /**
     * State untuk rapor yang sudah dirilis ke parent
     */
    public function released(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReportCard::STATUS_RELEASED,
            'generated_at' => now()->subDays(fake()->numberBetween(14, 21)),
            'generated_by' => User::factory()->state(['role' => 'ADMIN']),
            'approved_at' => now()->subDays(fake()->numberBetween(7, 14)),
            'approved_by' => User::factory()->state(['role' => 'PRINCIPAL']),
            'released_at' => now()->subDays(fake()->numberBetween(1, 7)),
            'pdf_path' => 'report-cards/'.fake()->uuid().'.pdf',
            'average_score' => fake()->randomFloat(2, 70, 95),
            'rank' => fake()->numberBetween(1, 30),
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
     * State dengan PDF sudah di-generate
     */
    public function withPdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'pdf_path' => 'report-cards/'.fake()->uuid().'.pdf',
        ]);
    }

    /**
     * State dengan average score dan rank
     */
    public function withScoreAndRank(?float $score = null, ?int $rank = null): static
    {
        return $this->state(fn (array $attributes) => [
            'average_score' => $score ?? fake()->randomFloat(2, 70, 95),
            'rank' => $rank ?? fake()->numberBetween(1, 30),
        ]);
    }
}
