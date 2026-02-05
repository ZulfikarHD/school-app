<?php

namespace Database\Factories;

use App\Enums\StatusKepegawaian;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    /**
     * Nama Indonesia yang umum untuk guru/dosen
     *
     * @var array<string>
     */
    private array $namaLakiLaki = [
        'Ahmad', 'Budi', 'Cahyo', 'Dedi', 'Eko', 'Fajar', 'Gunawan', 'Hadi',
        'Irwan', 'Joko', 'Kurniawan', 'Lukman', 'Muhammad', 'Nur', 'Oki',
        'Purnomo', 'Rahmat', 'Santoso', 'Teguh', 'Udin', 'Wahyu', 'Yusuf',
        'Zainal', 'Agung', 'Bambang', 'Dadang', 'Firdaus', 'Hendri', 'Iwan',
    ];

    /**
     * @var array<string>
     */
    private array $namaPerempuan = [
        'Ani', 'Dewi', 'Eka', 'Fitri', 'Gina', 'Hesti', 'Indah', 'Jasmine',
        'Kartini', 'Lestari', 'Maya', 'Novi', 'Oktavia', 'Putri', 'Ratna',
        'Sari', 'Titi', 'Umi', 'Vina', 'Wati', 'Yuni', 'Zahra', 'Ayu',
        'Bunga', 'Citra', 'Diana', 'Endang', 'Fatimah', 'Gita', 'Hana',
    ];

    /**
     * @var array<string>
     */
    private array $namaBelakang = [
        'Pratama', 'Putra', 'Wijaya', 'Kusuma', 'Santoso', 'Wibowo', 'Setiawan',
        'Hidayat', 'Saputra', 'Nugroho', 'Permana', 'Sutrisno', 'Rahayu',
        'Handayani', 'Susanto', 'Hartono', 'Suryadi', 'Maharani', 'Anggraini',
        'Puspita', 'Lestari', 'Utami', 'Suharto', 'Pranoto', 'Wulandari',
    ];

    /**
     * Define the model's default state untuk generate realistic teacher data
     * yang digunakan untuk testing dan seeding
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jenisKelamin = fake()->randomElement(['L', 'P']);
        $firstName = $jenisKelamin === 'L'
            ? fake()->randomElement($this->namaLakiLaki)
            : fake()->randomElement($this->namaPerempuan);
        $lastName = fake()->randomElement($this->namaBelakang);
        $namaLengkap = $firstName.' '.$lastName;

        // Generate tanggal lahir untuk usia 25-55 tahun
        $tanggalLahir = fake()->dateTimeBetween('-55 years', '-25 years');

        // Generate tanggal mulai kerja (1-20 tahun yang lalu)
        $tanggalMulaiKerja = fake()->dateTimeBetween('-20 years', '-1 year');

        $statusKepegawaian = fake()->randomElement(StatusKepegawaian::cases());

        return [
            'user_id' => null, // Will be assigned when creating with user
            'nip' => $statusKepegawaian === StatusKepegawaian::HONORER
                ? null
                : fake()->unique()->numerify('##################'), // 18 digits
            'nik' => fake()->unique()->numerify('################'), // 16 digits
            'nama_lengkap' => $namaLengkap,
            'tempat_lahir' => fake()->randomElement([
                'Jakarta', 'Bandung', 'Surabaya', 'Semarang', 'Yogyakarta',
                'Malang', 'Solo', 'Medan', 'Makassar', 'Palembang', 'Denpasar',
                'Bogor', 'Tangerang', 'Bekasi', 'Depok', 'Cirebon',
            ]),
            'tanggal_lahir' => $tanggalLahir,
            'jenis_kelamin' => $jenisKelamin,
            'alamat' => fake()->address(),
            'no_hp' => fake()->numerify('08##########'),
            'email' => fake()->unique()->safeEmail(),
            'foto' => null,
            'status_kepegawaian' => $statusKepegawaian->value,
            'tanggal_mulai_kerja' => $tanggalMulaiKerja,
            'tanggal_berakhir_kontrak' => $statusKepegawaian === StatusKepegawaian::KONTRAK
                ? fake()->dateTimeBetween('+1 month', '+2 years')
                : null,
            'kualifikasi_pendidikan' => fake()->randomElement(['S1', 'S2', 'S3', 'D3']),
            'is_active' => true,
        ];
    }

    /**
     * State untuk guru tetap (PNS)
     */
    public function tetap(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_kepegawaian' => StatusKepegawaian::TETAP->value,
            'nip' => fake()->unique()->numerify('##################'),
            'tanggal_berakhir_kontrak' => null,
        ]);
    }

    /**
     * State untuk guru honorer
     */
    public function honorer(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_kepegawaian' => StatusKepegawaian::HONORER->value,
            'nip' => null,
            'tanggal_berakhir_kontrak' => null,
        ]);
    }

    /**
     * State untuk guru kontrak
     */
    public function kontrak(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_kepegawaian' => StatusKepegawaian::KONTRAK->value,
            'nip' => fake()->unique()->numerify('##################'),
            'tanggal_berakhir_kontrak' => fake()->dateTimeBetween('+1 month', '+2 years'),
        ]);
    }

    /**
     * State untuk guru yang tidak aktif
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * State untuk guru laki-laki
     */
    public function male(): static
    {
        $firstName = fake()->randomElement($this->namaLakiLaki);
        $lastName = fake()->randomElement($this->namaBelakang);

        return $this->state(fn (array $attributes) => [
            'jenis_kelamin' => 'L',
            'nama_lengkap' => $firstName.' '.$lastName,
        ]);
    }

    /**
     * State untuk guru perempuan
     */
    public function female(): static
    {
        $firstName = fake()->randomElement($this->namaPerempuan);
        $lastName = fake()->randomElement($this->namaBelakang);

        return $this->state(fn (array $attributes) => [
            'jenis_kelamin' => 'P',
            'nama_lengkap' => $firstName.' '.$lastName,
        ]);
    }

    /**
     * State untuk guru dengan user account
     */
    public function withUser(): static
    {
        return $this->state(function (array $attributes) {
            $user = \App\Models\User::factory()->create([
                'name' => $attributes['nama_lengkap'],
                'email' => $attributes['email'],
                'role' => 'TEACHER',
                'status' => 'ACTIVE',
            ]);

            return [
                'user_id' => $user->id,
            ];
        });
    }
}
