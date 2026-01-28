<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk model PsbSetting
 * untuk generate konfigurasi PSB dalam testing
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PsbSetting>
 */
class PsbSettingFactory extends Factory
{
    /**
     * Define state default untuk konfigurasi PSB
     * dengan periode pendaftaran dan biaya
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $openDate = now()->addDays(fake()->numberBetween(-30, 30));

        return [
            'academic_year_id' => AcademicYear::factory(),
            'registration_open_date' => $openDate,
            'registration_close_date' => $openDate->copy()->addMonths(2),
            'announcement_date' => $openDate->copy()->addMonths(3),
            're_registration_deadline_days' => 7,
            'registration_fee' => fake()->randomElement([0, 100000, 150000, 200000]),
            'quota_per_class' => fake()->numberBetween(25, 35),
            'waiting_list_enabled' => true,
        ];
    }

    /**
     * State untuk pendaftaran yang sedang dibuka
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_open_date' => now()->subDays(7),
            'registration_close_date' => now()->addDays(30),
        ]);
    }

    /**
     * State untuk pendaftaran yang sudah ditutup
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_open_date' => now()->subMonths(2),
            'registration_close_date' => now()->subDays(7),
        ]);
    }

    /**
     * State untuk pendaftaran gratis (tanpa biaya)
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_fee' => 0,
        ]);
    }
}
