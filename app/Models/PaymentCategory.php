<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model PaymentCategory untuk mengelola kategori pembayaran sekolah
 *
 * Kategori mencakup SPP, Uang Gedung, Seragam, Kegiatan, dan Donasi
 * dengan support untuk konfigurasi harga per kelas
 */
class PaymentCategory extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentCategoryFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes untuk kategori pembayaran
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'kode',
        'deskripsi',
        'tipe',
        'nominal_default',
        'is_active',
        'is_mandatory',
        'due_day',
        'tahun_ajaran',
        'created_by',
        'updated_by',
    ];

    /**
     * Cast attributes ke tipe data yang sesuai
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal_default' => 'decimal:2',
            'is_active' => 'boolean',
            'is_mandatory' => 'boolean',
            'due_day' => 'integer',
        ];
    }

    /**
     * Boot method untuk auto-fill created_by dan updated_by
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    /**
     * Relationship many-to-one dengan User (creator)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship many-to-one dengan User (updater)
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relationship one-to-many dengan Bills
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Relationship one-to-many dengan PaymentCategoryClassPrice
     * untuk konfigurasi harga per kelas
     */
    public function classPrices(): HasMany
    {
        return $this->hasMany(PaymentCategoryClassPrice::class);
    }

    /**
     * Scope query untuk filter kategori aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query untuk filter berdasarkan tipe pembayaran
     */
    public function scopeByType($query, string $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    /**
     * Scope query untuk filter berdasarkan tahun ajaran
     */
    public function scopeByAcademicYear($query, string $tahunAjaran)
    {
        return $query->where('tahun_ajaran', $tahunAjaran);
    }

    /**
     * Scope query untuk filter kategori wajib
     */
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * Helper method untuk mendapatkan harga berdasarkan kelas
     * Jika tidak ada konfigurasi khusus, return nominal_default
     */
    public function getPriceForClass(?int $classId): float
    {
        if (! $classId) {
            return (float) $this->nominal_default;
        }

        $classPrice = $this->classPrices()->where('class_id', $classId)->first();

        return $classPrice ? (float) $classPrice->nominal : (float) $this->nominal_default;
    }

    /**
     * Helper method untuk format nominal ke Rupiah
     */
    public function getFormattedNominalAttribute(): string
    {
        return 'Rp '.number_format($this->nominal_default, 0, ',', '.');
    }

    /**
     * Helper method untuk mendapatkan label tipe yang readable
     */
    public function getTipeLabelAttribute(): string
    {
        return match ($this->tipe) {
            'bulanan' => 'Bulanan',
            'tahunan' => 'Tahunan',
            'insidental' => 'Insidental',
            default => $this->tipe,
        };
    }
}
