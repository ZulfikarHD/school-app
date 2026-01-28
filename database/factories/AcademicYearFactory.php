<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk model AcademicYear
 * untuk generate data dummy tahun ajaran dalam testing
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    /**
     * Define state default untuk tahun ajaran
     * dengan format name 2025/2026 dan periode Juli-Juni
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(2024, 2026);
        $startDate = "{$year}-07-01";
        $endDate = ($year + 1).'-06-30';

        return [
            'name' => "{$year}/".($year + 1),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => false,
        ];
    }

    /**
     * State untuk tahun ajaran yang aktif
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * State untuk tahun ajaran saat ini (2025/2026)
     */
    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);
    }
}
