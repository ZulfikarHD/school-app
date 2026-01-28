<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\PsbRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk model PsbRegistration
 * untuk generate data pendaftaran PSB dalam testing
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PsbRegistration>
 */
class PsbRegistrationFactory extends Factory
{
    /**
     * Counter untuk generate nomor pendaftaran
     */
    private static int $counter = 0;

    /**
     * Define state default untuk pendaftaran PSB
     * dengan data lengkap siswa dan orang tua
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        self::$counter++;
        $year = now()->year;
        $gender = fake()->randomElement(['L', 'P']);

        return [
            'registration_number' => sprintf('PSB/%d/%04d', $year, self::$counter),
            'academic_year_id' => AcademicYear::factory(),
            'status' => PsbRegistration::STATUS_PENDING,
            // Data Siswa
            'student_name' => fake()->name($gender === 'L' ? 'male' : 'female'),
            'student_nik' => fake()->numerify('################'),
            'birth_place' => fake()->city(),
            'birth_date' => fake()->dateTimeBetween('-8 years', '-6 years'),
            'gender' => $gender,
            'religion' => fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            'address' => fake()->address(),
            'child_order' => fake()->numberBetween(1, 5),
            'origin_school' => 'TK '.fake()->company(),
            // Data Ayah
            'father_name' => fake()->name('male'),
            'father_nik' => fake()->numerify('################'),
            'father_occupation' => fake()->randomElement(['PNS', 'Wiraswasta', 'Karyawan Swasta', 'Pedagang', 'Buruh', 'Petani']),
            'father_phone' => fake()->numerify('08##########'),
            'father_email' => fake()->optional()->safeEmail(),
            // Data Ibu
            'mother_name' => fake()->name('female'),
            'mother_nik' => fake()->numerify('################'),
            'mother_occupation' => fake()->randomElement(['Ibu Rumah Tangga', 'PNS', 'Wiraswasta', 'Karyawan Swasta', 'Pedagang']),
            'mother_phone' => fake()->optional()->numerify('08##########'),
            'mother_email' => fake()->optional()->safeEmail(),
            // Lainnya
            'notes' => null,
            'rejection_reason' => null,
            'verified_by' => null,
            'verified_at' => null,
            'announced_at' => null,
        ];
    }

    /**
     * State untuk pendaftaran yang sudah diverifikasi dokumen
     */
    public function documentReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PsbRegistration::STATUS_DOCUMENT_REVIEW,
        ]);
    }

    /**
     * State untuk pendaftaran yang diterima
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PsbRegistration::STATUS_APPROVED,
            'verified_at' => now(),
            'announced_at' => now(),
        ]);
    }

    /**
     * State untuk pendaftaran yang ditolak
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PsbRegistration::STATUS_REJECTED,
            'rejection_reason' => fake()->sentence(),
            'verified_at' => now(),
            'announced_at' => now(),
        ]);
    }

    /**
     * State untuk waiting list
     */
    public function waitingList(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PsbRegistration::STATUS_WAITING_LIST,
            'announced_at' => now(),
        ]);
    }

    /**
     * State untuk tahap daftar ulang
     */
    public function reRegistration(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PsbRegistration::STATUS_RE_REGISTRATION,
            'verified_at' => now(),
            'announced_at' => now(),
        ]);
    }

    /**
     * State untuk pendaftaran selesai
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PsbRegistration::STATUS_COMPLETED,
            'verified_at' => now()->subDays(7),
            'announced_at' => now()->subDays(5),
        ]);
    }
}
