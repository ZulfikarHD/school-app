<?php

namespace Database\Factories;

use App\Models\GradeWeightConfig;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GradeWeightConfig>
 */
class GradeWeightConfigFactory extends Factory
{
    protected $model = GradeWeightConfig::class;

    /**
     * Define the model's default state untuk generate grade weight config
     * dengan bobot default sesuai standar K13
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tahun_ajaran' => '2024/2025',
            'subject_id' => null,
            'uh_weight' => GradeWeightConfig::DEFAULT_UH_WEIGHT,
            'uts_weight' => GradeWeightConfig::DEFAULT_UTS_WEIGHT,
            'uas_weight' => GradeWeightConfig::DEFAULT_UAS_WEIGHT,
            'praktik_weight' => GradeWeightConfig::DEFAULT_PRAKTIK_WEIGHT,
            'is_default' => true,
        ];
    }

    /**
     * State untuk konfigurasi default (tidak terkait mapel tertentu)
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject_id' => null,
            'is_default' => true,
        ]);
    }

    /**
     * State untuk konfigurasi per mata pelajaran
     */
    public function forSubject(?int $subjectId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'subject_id' => $subjectId ?? Subject::factory(),
            'is_default' => false,
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
     * State untuk bobot dengan penekanan pada UH
     * UH: 40%, UTS: 20%, UAS: 25%, Praktik: 15%
     */
    public function emphasizeUh(): static
    {
        return $this->state(fn (array $attributes) => [
            'uh_weight' => 40,
            'uts_weight' => 20,
            'uas_weight' => 25,
            'praktik_weight' => 15,
        ]);
    }

    /**
     * State untuk bobot dengan penekanan pada UAS
     * UH: 25%, UTS: 20%, UAS: 40%, Praktik: 15%
     */
    public function emphasizeUas(): static
    {
        return $this->state(fn (array $attributes) => [
            'uh_weight' => 25,
            'uts_weight' => 20,
            'uas_weight' => 40,
            'praktik_weight' => 15,
        ]);
    }

    /**
     * State untuk bobot dengan penekanan pada praktik (cocok untuk mapel PJOK, Seni)
     * UH: 20%, UTS: 15%, UAS: 25%, Praktik: 40%
     */
    public function emphasizePraktik(): static
    {
        return $this->state(fn (array $attributes) => [
            'uh_weight' => 20,
            'uts_weight' => 15,
            'uas_weight' => 25,
            'praktik_weight' => 40,
        ]);
    }

    /**
     * State untuk bobot custom
     * Total harus 100%
     */
    public function customWeight(int $uh, int $uts, int $uas, int $praktik): static
    {
        return $this->state(fn (array $attributes) => [
            'uh_weight' => $uh,
            'uts_weight' => $uts,
            'uas_weight' => $uas,
            'praktik_weight' => $praktik,
        ]);
    }

    /**
     * State untuk bobot tidak valid (total != 100%)
     * Digunakan untuk testing validasi
     */
    public function invalidWeight(): static
    {
        return $this->state(fn (array $attributes) => [
            'uh_weight' => 30,
            'uts_weight' => 30,
            'uas_weight' => 30,
            'praktik_weight' => 30, // Total = 120%
        ]);
    }
}
