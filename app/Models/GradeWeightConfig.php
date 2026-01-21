<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class GradeWeightConfig extends Model
{
    /** @use HasFactory<\Database\Factories\GradeWeightConfigFactory> */
    use HasFactory;

    /**
     * Default bobot nilai sesuai standar K13
     */
    public const DEFAULT_UH_WEIGHT = 30;

    public const DEFAULT_UTS_WEIGHT = 25;

    public const DEFAULT_UAS_WEIGHT = 30;

    public const DEFAULT_PRAKTIK_WEIGHT = 15;

    /**
     * Mass assignable attributes untuk grade weight config data
     * yang mencakup bobot untuk setiap komponen penilaian
     *
     * @var list<string>
     */
    protected $fillable = [
        'tahun_ajaran',
        'subject_id',
        'uh_weight',
        'uts_weight',
        'uas_weight',
        'praktik_weight',
        'is_default',
    ];

    /**
     * Cast attributes ke tipe data yang sesuai
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'uh_weight' => 'integer',
            'uts_weight' => 'integer',
            'uas_weight' => 'integer',
            'praktik_weight' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Relationship many-to-one dengan Subject
     * dimana satu config bisa terkait dengan satu mata pelajaran (optional)
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Scope query untuk filter berdasarkan tahun ajaran
     *
     * @param  Builder<GradeWeightConfig>  $query
     * @return Builder<GradeWeightConfig>
     */
    public function scopeByTahunAjaran(Builder $query, string $tahunAjaran): Builder
    {
        return $query->where('tahun_ajaran', $tahunAjaran);
    }

    /**
     * Scope query untuk filter konfigurasi default
     *
     * @param  Builder<GradeWeightConfig>  $query
     * @return Builder<GradeWeightConfig>
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope query untuk filter konfigurasi per mapel
     *
     * @param  Builder<GradeWeightConfig>  $query
     * @return Builder<GradeWeightConfig>
     */
    public function scopeBySubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Accessor untuk mendapatkan total bobot
     */
    public function getTotalWeightAttribute(): int
    {
        return $this->uh_weight + $this->uts_weight + $this->uas_weight + $this->praktik_weight;
    }

    /**
     * Accessor untuk check apakah total bobot valid (= 100%)
     */
    public function getIsValidAttribute(): bool
    {
        return $this->total_weight === 100;
    }

    /**
     * Validasi bahwa total bobot = 100%
     *
     * @throws InvalidArgumentException
     */
    public function validateTotalWeight(): bool
    {
        if ($this->total_weight !== 100) {
            throw new InvalidArgumentException(
                "Total bobot harus 100%, saat ini: {$this->total_weight}%"
            );
        }

        return true;
    }

    /**
     * Static helper untuk mendapatkan konfigurasi aktif untuk suatu mapel
     * Prioritas: 1) Config per mapel, 2) Config default tahun ajaran
     */
    public static function getActiveConfig(string $tahunAjaran, ?int $subjectId = null): ?self
    {
        // Coba cari config spesifik untuk mapel ini
        if ($subjectId) {
            $config = self::byTahunAjaran($tahunAjaran)
                ->bySubject($subjectId)
                ->first();

            if ($config) {
                return $config;
            }
        }

        // Fallback ke config default
        return self::byTahunAjaran($tahunAjaran)
            ->default()
            ->first();
    }

    /**
     * Static helper untuk membuat atau mendapatkan default config
     */
    public static function getOrCreateDefault(string $tahunAjaran): self
    {
        return self::firstOrCreate(
            [
                'tahun_ajaran' => $tahunAjaran,
                'is_default' => true,
            ],
            [
                'subject_id' => null,
                'uh_weight' => self::DEFAULT_UH_WEIGHT,
                'uts_weight' => self::DEFAULT_UTS_WEIGHT,
                'uas_weight' => self::DEFAULT_UAS_WEIGHT,
                'praktik_weight' => self::DEFAULT_PRAKTIK_WEIGHT,
            ]
        );
    }

    /**
     * Helper method untuk menghitung nilai akhir berdasarkan komponen
     *
     * @param  float  $uhAverage  Rata-rata nilai UH
     * @param  float  $utsScore  Nilai UTS
     * @param  float  $uasScore  Nilai UAS
     * @param  float  $praktikAverage  Rata-rata nilai praktik
     */
    public function calculateFinalGrade(
        float $uhAverage,
        float $utsScore,
        float $uasScore,
        float $praktikAverage = 0
    ): float {
        $finalGrade = ($uhAverage * $this->uh_weight / 100)
            + ($utsScore * $this->uts_weight / 100)
            + ($uasScore * $this->uas_weight / 100)
            + ($praktikAverage * $this->praktik_weight / 100);

        return round($finalGrade, 2);
    }

    /**
     * Static helper untuk mendapatkan list bobot dengan label
     *
     * @return array<string, array{weight: int, label: string}>
     */
    public function getWeightBreakdown(): array
    {
        return [
            'uh' => [
                'weight' => $this->uh_weight,
                'label' => 'Ulangan Harian',
            ],
            'uts' => [
                'weight' => $this->uts_weight,
                'label' => 'Ujian Tengah Semester',
            ],
            'uas' => [
                'weight' => $this->uas_weight,
                'label' => 'Ujian Akhir Semester',
            ],
            'praktik' => [
                'weight' => $this->praktik_weight,
                'label' => 'Praktik/Proyek',
            ],
        ];
    }
}
