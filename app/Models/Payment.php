<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Payment untuk mengelola pembayaran siswa
 *
 * Setiap pembayaran terhubung dengan tagihan (Bill)
 * dengan support untuk multiple payment methods dan verification flow
 */
class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes untuk pembayaran
     *
     * @var list<string>
     */
    protected $fillable = [
        'nomor_kwitansi',
        'bill_id',
        'student_id',
        'nominal',
        'metode_pembayaran',
        'tanggal_bayar',
        'waktu_bayar',
        'status',
        'bukti_transfer',
        'keterangan',
        'verified_by',
        'verified_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
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
            'tanggal_bayar' => 'date',
            'waktu_bayar' => 'datetime:H:i',
            'verified_at' => 'datetime',
            'cancelled_at' => 'datetime',
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

        // Update bill status setelah payment created/updated
        static::saved(function ($model) {
            $model->bill->updatePaymentStatus();
        });

        // Update bill status setelah payment deleted
        static::deleted(function ($model) {
            $model->bill->updatePaymentStatus();
        });
    }

    /**
     * Relationship many-to-one dengan Bill
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Relationship many-to-one dengan Student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relationship many-to-one dengan User (creator)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship many-to-one dengan User (verifier)
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Relationship many-to-one dengan User (canceller)
     */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Scope query untuk pembayaran verified
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope query untuk pembayaran pending verification
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope query untuk pembayaran aktif (tidak dibatalkan)
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    /**
     * Scope query untuk filter berdasarkan siswa
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope query untuk filter berdasarkan tanggal
     */
    public function scopeOnDate($query, string $date)
    {
        return $query->where('tanggal_bayar', $date);
    }

    /**
     * Scope query untuk filter berdasarkan range tanggal
     */
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('tanggal_bayar', [$startDate, $endDate]);
    }

    /**
     * Scope query untuk filter berdasarkan metode pembayaran
     */
    public function scopeByMethod($query, string $metode)
    {
        return $query->where('metode_pembayaran', $metode);
    }

    /**
     * Helper method untuk format nominal ke Rupiah
     */
    public function getFormattedNominalAttribute(): string
    {
        return 'Rp '.number_format($this->nominal, 0, ',', '.');
    }

    /**
     * Helper method untuk mendapatkan label status yang readable
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Verifikasi',
            'verified' => 'Terverifikasi',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    /**
     * Helper method untuk mendapatkan label metode pembayaran
     */
    public function getMetodeLabelAttribute(): string
    {
        return match ($this->metode_pembayaran) {
            'tunai' => 'Tunai',
            'transfer' => 'Transfer Bank',
            'qris' => 'QRIS',
            default => $this->metode_pembayaran,
        };
    }

    /**
     * Helper method untuk check apakah pembayaran bisa diverifikasi
     */
    public function canBeVerified(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Helper method untuk check apakah pembayaran bisa dibatalkan
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'verified']);
    }

    /**
     * Verify pembayaran
     */
    public function verify(?int $userId = null): bool
    {
        if (! $this->canBeVerified()) {
            return false;
        }

        $this->status = 'verified';
        $this->verified_by = $userId ?? auth()->id();
        $this->verified_at = now();

        return $this->save();
    }

    /**
     * Cancel pembayaran
     */
    public function cancel(string $reason, ?int $userId = null): bool
    {
        if (! $this->canBeCancelled()) {
            return false;
        }

        $this->status = 'cancelled';
        $this->cancelled_by = $userId ?? auth()->id();
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;

        return $this->save();
    }

    /**
     * Generate nomor kwitansi otomatis
     */
    public static function generateNomorKwitansi(): string
    {
        $prefix = 'KWT';
        $year = now()->format('Y');
        $month = now()->format('m');

        $lastPayment = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastPayment ? ((int) substr($lastPayment->nomor_kwitansi, -5)) + 1 : 1;

        return sprintf('%s/%s/%s/%05d', $prefix, $year, $month, $sequence);
    }
}
