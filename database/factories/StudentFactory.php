<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    /**
     * Define the model's default state untuk generate realistic student data
     * yang digunakan untuk testing dan seeding
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jenisKelamin = fake()->randomElement(['L', 'P']);
        $firstName = $jenisKelamin === 'L' ? fake()->firstNameMale() : fake()->firstNameFemale();
        $lastName = fake()->lastName();
        $namaLengkap = $firstName.' '.$lastName;

        // Generate NIS dengan format tahun + 4 digit
        $year = now()->year;
        $nis = $year.str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT);

        return [
            'nis' => $nis,
            'nisn' => fake()->unique()->numerify('##########'), // 10 digits
            'nik' => fake()->unique()->numerify('################'), // 16 digits
            'nama_lengkap' => $namaLengkap,
            'nama_panggilan' => $firstName,
            'jenis_kelamin' => $jenisKelamin,
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->dateTimeBetween('-12 years', '-6 years'),
            'agama' => fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
            'anak_ke' => fake()->numberBetween(1, 5),
            'jumlah_saudara' => fake()->numberBetween(1, 5),
            'status_keluarga' => fake()->randomElement(['Anak Kandung', 'Anak Tiri', 'Anak Angkat']),
            'alamat' => fake()->address(),
            'rt_rw' => fake()->numerify('###/###'),
            'kelurahan' => fake()->streetName(),
            'kecamatan' => fake()->streetName(),
            'kota' => fake()->city(),
            'provinsi' => fake()->state(),
            'kode_pos' => fake()->postcode(),
            'no_hp' => fake()->numerify('08##########'),
            'email' => fake()->optional()->safeEmail(),
            'foto' => null,
            'kelas_id' => fake()->numberBetween(1, 12), // Assuming 12 classes
            'tahun_ajaran_masuk' => $year.'/'.(($year + 1)),
            'tanggal_masuk' => fake()->dateTimeBetween('-1 year', 'now'),
            'status' => 'aktif',
        ];
    }

    /**
     * State untuk siswa yang sudah mutasi
     */
    public function mutasi(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'mutasi',
        ]);
    }

    /**
     * State untuk siswa yang DO
     */
    public function dropout(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'do',
        ]);
    }

    /**
     * State untuk siswa yang lulus
     */
    public function lulus(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'lulus',
        ]);
    }

    /**
     * State untuk siswa laki-laki
     */
    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_kelamin' => 'L',
        ]);
    }

    /**
     * State untuk siswa perempuan
     */
    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'jenis_kelamin' => 'P',
        ]);
    }
}
