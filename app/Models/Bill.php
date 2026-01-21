<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Bill untuk mengelola tagihan siswa
 *
 * Setiap tagihan di-generate berdasarkan kategori pembayaran
 * dengan tracking status dan support partial payment
 */
class Bill extends Model
{
    /** @use HasFactory<\Database\Factories\BillFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes untuk tagihan
     *
     * @var list<string>
     */
    protected $fillable = [
        'nomor_tagihan',
        'student_id',
        'payment_category_id',
        'tahun_ajaran',
        'bulan',
        'tahun',
        'nominal',
        'nominal_terbayar',
        'status',
        'tanggal_jatuh_tempo',
        'keterangan',
        'created_by',
    ];

    /**
     * Cast attributes ke tipe data yang sesuai
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'nominal_terbayar' => 'decimal:2',
            'bulan' => 'integer',
            'tahun' => 'integer',
            'tanggal_jatuh_tempo' => 'date',
        ];
    }

    /**
     * Boot method untuk auto-fill created_by
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }

    /**
     * Relationship many-to-one dengan Student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relationship many-to-one dengan PaymentCategory
     */
    public function paymentCategory(): BelongsTo
    {
        return $this->belongsTo(PaymentCategory::class);
    }

    /**
     * Relationship many-to-one dengan User (creator)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship one-to-many dengan Payment (legacy)
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relationship one-to-many dengan PaymentItem
     * untuk combined payment system
     */
    public function paymentItems(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
    }

    /**
     * Scope query untuk filter tagihan belum lunas
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['belum_bayar', 'sebagian']);
    }

    /**
     * Scope query untuk filter tagihan lunas
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'lunas');
    }

    /**
     * Scope query untuk filter tagihan aktif (tidak dibatalkan)
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'dibatalkan');
    }

    /**
     * Scope query untuk filter berdasarkan siswa
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope query untuk filter berdasarkan bulan dan tahun
     */
    public function scopeForPeriod($query, int $bulan, int $tahun)
    {
        return $query->where('bulan', $bulan)->where('tahun', $tahun);
    }

    /**
     * Scope query untuk filter berdasarkan tahun ajaran
     */
    public function scopeByAcademicYear($query, string $tahunAjaran)
    {
        return $query->where('tahun_ajaran', $tahunAjaran);
    }

    /**
     * Scope query untuk tagihan yang sudah jatuh tempo
     */
    public function scopeOverdue($query)
    {
        return $query->unpaid()
            ->where('tanggal_jatuh_tempo', '<', now()->toDateString());
    }

    /**
     * Helper method untuk menghitung sisa tagihan
     */
    public function getSisaTagihanAttribute(): float
    {
        return (float) $this->nominal - (float) $this->nominal_terbayar;
    }

    /**
     * Helper method untuk format nominal ke Rupiah
     */
    public function getFormattedNominalAttribute(): string
    {
        return 'Rp '.number_format($this->nominal, 0, ',', '.');
    }

    /**
     * Helper method untuk format sisa tagihan ke Rupiah
     */
    public function getFormattedSisaAttribute(): string
    {
        return 'Rp '.number_format($this->sisa_tagihan, 0, ',', '.');
    }

    /**
     * Helper method untuk mendapatkan label status yang readable
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'belum_bayar' => 'Belum Bayar',
            'sebagian' => 'Sebagian',
            'lunas' => 'Lunas',
            'dibatalkan' => 'Dibatalkan',
            default => $this->status,
        };
    }

    /**
     * Helper method untuk mendapatkan nama bulan
     */
    public function getNamaBulanAttribute(): ?string
    {
        if (! $this->bulan) {
            return null;
        }

        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $bulanNames[$this->bulan] ?? null;
    }

    /**
     * Helper method untuk check apakah tagihan sudah jatuh tempo
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'lunas'
            && $this->status !== 'dibatalkan'
            && $this->tanggal_jatuh_tempo < now()->toDateString();
    }

    /**
     * Helper method untuk check apakah tagihan bisa dibayar
     */
    public function canBePaid(): bool
    {
        return in_array($this->status, ['belum_bayar', 'sebagian']);
    }

    /**
     * Update status tagihan berdasarkan nominal terbayar
     * dari legacy Payment model
     */
    public function updatePaymentStatus(): void
    {
        $totalPaid = $this->payments()
            ->where('status', 'verified')
            ->sum('nominal');

        $this->nominal_terbayar = $totalPaid;

        if ($totalPaid >= $this->nominal) {
            $this->status = 'lunas';
        } elseif ($totalPaid > 0) {
            $this->status = 'sebagian';
        } else {
            $this->status = 'belum_bayar';
        }

        $this->save();
    }

    /**
     * Update status tagihan berdasarkan PaymentItem dari PaymentTransaction
     *
     * Menghitung total pembayaran dari verified PaymentItems
     * dan mengupdate status tagihan sesuai dengan nominal terbayar
     */
    public function updatePaymentStatusFromTransaction(): void
    {
        // Hitung total dari PaymentItems yang transaction-nya verified
        $totalFromTransactions = $this->paymentItems()
            ->whereHas('paymentTransaction', function ($query) {
                $query->where('status', 'verified');
            })
            ->sum('amount');

        // Hitung total dari legacy Payments yang verified (untuk backward compatibility)
        $totalFromLegacy = $this->payments()
            ->where('status', 'verified')
            ->sum('nominal');

        $totalPaid = $totalFromTransactions + $totalFromLegacy;

        $this->nominal_terbayar = $totalPaid;

        if ($totalPaid >= $this->nominal) {
            $this->status = 'lunas';
        } elseif ($totalPaid > 0) {
            $this->status = 'sebagian';
        } else {
            $this->status = 'belum_bayar';
        }

        $this->save();
    }

    /**
     * Generate nomor tagihan otomatis
     */
    public static function generateNomorTagihan(): string
    {
        $prefix = 'INV';
        $year = now()->format('Y');
        $month = now()->format('m');

        $lastBill = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastBill ? ((int) substr($lastBill->nomor_tagihan, -5)) + 1 : 1;

        return sprintf('%s/%s/%s/%05d', $prefix, $year, $month, $sequence);
    }
}
