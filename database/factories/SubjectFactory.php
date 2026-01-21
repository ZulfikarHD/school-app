<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state untuk generate data mata pelajaran
     * yang realistis untuk keperluan testing
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subjects = [
            ['kode' => 'MTK', 'nama' => 'Matematika', 'kategori' => 'UTAMA'],
            ['kode' => 'BIN', 'nama' => 'Bahasa Indonesia', 'kategori' => 'UTAMA'],
            ['kode' => 'IPA', 'nama' => 'Ilmu Pengetahuan Alam', 'kategori' => 'UTAMA'],
            ['kode' => 'IPS', 'nama' => 'Ilmu Pengetahuan Sosial', 'kategori' => 'UTAMA'],
            ['kode' => 'PKN', 'nama' => 'Pendidikan Kewarganegaraan', 'kategori' => 'UTAMA'],
            ['kode' => 'BIG', 'nama' => 'Bahasa Inggris', 'kategori' => 'MUATAN_LOKAL'],
            ['kode' => 'SBK', 'nama' => 'Seni Budaya dan Keterampilan', 'kategori' => 'PENGEMBANGAN_DIRI'],
            ['kode' => 'PJOK', 'nama' => 'Pendidikan Jasmani', 'kategori' => 'UTAMA'],
            ['kode' => 'PAI', 'nama' => 'Pendidikan Agama Islam', 'kategori' => 'UTAMA'],
        ];

        $subject = fake()->randomElement($subjects);
        $uniqueCode = $subject['kode'].'-'.fake()->unique()->numberBetween(1, 9999);

        return [
            'kode_mapel' => $uniqueCode,
            'nama_mapel' => $subject['nama'],
            'tingkat' => fake()->optional(0.5)->numberBetween(1, 6),
            'kategori' => $subject['kategori'],
            'is_active' => true,
        ];
    }

    /**
     * State untuk mata pelajaran yang tidak aktif
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * State untuk mata pelajaran tingkat tertentu
     */
    public function forTingkat(int $tingkat): static
    {
        return $this->state(fn (array $attributes) => [
            'tingkat' => $tingkat,
        ]);
    }

    /**
     * State untuk mata pelajaran Matematika
     */
    public function matematika(): static
    {
        return $this->state(fn (array $attributes) => [
            'kode_mapel' => 'MTK-'.fake()->unique()->numberBetween(1, 9999),
            'nama_mapel' => 'Matematika',
            'kategori' => 'UTAMA',
        ]);
    }

    /**
     * State untuk mata pelajaran Bahasa Indonesia
     */
    public function bahasaIndonesia(): static
    {
        return $this->state(fn (array $attributes) => [
            'kode_mapel' => 'BIN-'.fake()->unique()->numberBetween(1, 9999),
            'nama_mapel' => 'Bahasa Indonesia',
            'kategori' => 'UTAMA',
        ]);
    }
}
