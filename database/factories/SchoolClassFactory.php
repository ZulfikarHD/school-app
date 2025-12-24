<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tingkat' => $this->faker->numberBetween(1, 6),
            'nama' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'kapasitas' => 40,
            'tahun_ajaran' => '2024/2025',
            'is_active' => true,
        ];
    }

    /**
     * State untuk kelas dengan tingkat tertentu
     */
    public function tingkat(int $tingkat): static
    {
        return $this->state(fn (array $attributes) => [
            'tingkat' => $tingkat,
        ]);
    }

    /**
     * State untuk kelas dengan nama tertentu (A, B, C, D)
     */
    public function nama(string $nama): static
    {
        return $this->state(fn (array $attributes) => [
            'nama' => $nama,
        ]);
    }

    /**
     * State untuk kelas dengan tahun ajaran tertentu
     */
    public function tahunAjaran(string $tahunAjaran): static
    {
        return $this->state(fn (array $attributes) => [
            'tahun_ajaran' => $tahunAjaran,
        ]);
    }

    /**
     * State untuk kelas tidak aktif
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * State untuk kelas dengan wali kelas
     */
    public function withWaliKelas(?User $teacher = null): static
    {
        return $this->state(function (array $attributes) use ($teacher) {
            if (! $teacher) {
                $teacher = User::factory()->create([
                    'role' => 'TEACHER',
                    'status' => 'ACTIVE',
                ]);
            }

            return [
                'wali_kelas_id' => $teacher->id,
            ];
        });
    }

    /**
     * State untuk kelas dengan kapasitas tertentu
     */
    public function kapasitas(int $kapasitas): static
    {
        return $this->state(fn (array $attributes) => [
            'kapasitas' => $kapasitas,
        ]);
    }
}
