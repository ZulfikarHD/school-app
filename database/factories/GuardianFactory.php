<?php

namespace Database\Factories;

use App\Models\Guardian;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guardian>
 */
class GuardianFactory extends Factory
{
    protected $model = Guardian::class;

    /**
     * Define the model's default state untuk generate realistic guardian data
     * yang digunakan untuk testing dan seeding
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hubungan = fake()->randomElement(['ayah', 'ibu', 'wali']);

        // Generate nama sesuai hubungan
        if ($hubungan === 'ayah') {
            $namaLengkap = fake()->firstNameMale().' '.fake()->lastName();
        } elseif ($hubungan === 'ibu') {
            $namaLengkap = fake()->firstNameFemale().' '.fake()->lastName();
        } else {
            $namaLengkap = fake()->name();
        }

        return [
            'nik' => fake()->unique()->numerify('################'), // 16 digits
            'nama_lengkap' => $namaLengkap,
            'hubungan' => $hubungan,
            'pekerjaan' => fake()->randomElement([
                'PNS',
                'Pegawai Swasta',
                'Wiraswasta',
                'Guru',
                'Dokter',
                'Petani',
                'Buruh',
                'Ibu Rumah Tangga',
                'Pedagang',
                'TNI/Polri',
            ]),
            'pendidikan' => fake()->randomElement(['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3']),
            'penghasilan' => fake()->randomElement(['<1jt', '1-3jt', '3-5jt', '>5jt']),
            'no_hp' => fake()->numerify('08##########'),
            'email' => fake()->optional()->safeEmail(),
            'alamat' => fake()->address(),
            'user_id' => null, // Will be set when creating parent account
        ];
    }

    /**
     * State untuk guardian sebagai ayah
     */
    public function ayah(): static
    {
        return $this->state(fn (array $attributes) => [
            'hubungan' => 'ayah',
            'nama_lengkap' => fake()->firstNameMale().' '.fake()->lastName(),
        ]);
    }

    /**
     * State untuk guardian sebagai ibu
     */
    public function ibu(): static
    {
        return $this->state(fn (array $attributes) => [
            'hubungan' => 'ibu',
            'nama_lengkap' => fake()->firstNameFemale().' '.fake()->lastName(),
            'pekerjaan' => fake()->randomElement(['Ibu Rumah Tangga', 'Guru', 'Pegawai Swasta', 'Wiraswasta']),
        ]);
    }

    /**
     * State untuk guardian sebagai wali
     */
    public function wali(): static
    {
        return $this->state(fn (array $attributes) => [
            'hubungan' => 'wali',
        ]);
    }

    /**
     * State untuk guardian yang sudah punya portal account
     */
    public function withPortalAccount(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => \App\Models\User::factory()->create([
                'role' => 'PARENT',
                'status' => 'ACTIVE',
            ])->id,
        ]);
    }
}
