<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model PaymentTransaction untuk mengelola transaksi pembayaran
 *
 * Satu transaksi bisa mencakup pembayaran untuk multiple tagihan (bills)
 * dengan satu bukti transfer dan satu proses verifikasi.
 * Refactor dari model Payment yang 1:1 dengan Bill.
 */
class PaymentTransaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes untuk transaksi pembayaran
     *
     * @var list<string>
     */
    protected $fillable = [
        'transaction_number',
        'guardian_id',
        'total_amount',
        'payment_method',
        'payment_date',
        'payment_time',
        'proof_file',
        'notes',
        'status',
        'verified_by',
        'verified_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
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
            'total_amount' => 'decimal:2',
            'payment_date' => 'date',
            'payment_time' => 'datetime:H:i',
            'verified_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Boot method untuk auto-fill created_by dan updated_by
     *
     * Secara otomatis mencatat user yang membuat dan mengupdate
     * data transaksi untuk keperluan audit trail
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

        // Update semua bill status setelah transaction status berubah
        static::saved(function ($model) {
            $model->updateAllBillStatuses();
        });
    }

    // ===================================================================
    // RELATIONSHIPS
    // ===================================================================

    /**
     * Relationship many-to-one dengan Guardian
     * untuk tracking parent yang submit pembayaran
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    /**
     * Relationship one-to-many dengan PaymentItem
     * satu transaksi memiliki multiple payment items
     */
    public function items(): HasMany
    {
        return $this->hasMany(PaymentItem::class);
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
     * Relationship many-to-one dengan User (updater)
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ===================================================================
    // SCOPES
    // ===================================================================

    /**
     * Scope query untuk transaksi verified
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope query untuk transaksi pending verification
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope query untuk transaksi aktif (tidak dibatalkan)
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    /**
     * Scope query untuk filter berdasarkan guardian
     */
    public function scopeForGuardian($query, int $guardianId)
    {
        return $query->where('guardian_id', $guardianId);
    }

    /**
     * Scope query untuk filter berdasarkan student
     * melalui payment items
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->whereHas('items', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        });
    }

    /**
     * Scope query untuk filter berdasarkan tanggal
     */
    public function scopeOnDate($query, string $date)
    {
        return $query->where('payment_date', $date);
    }

    /**
     * Scope query untuk filter berdasarkan range tanggal
     */
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Scope query untuk filter berdasarkan metode pembayaran
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    // ===================================================================
    // ACCESSORS
    // ===================================================================

    /**
     * Helper method untuk format total_amount ke Rupiah
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp '.number_format($this->total_amount, 0, ',', '.');
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
    public function getMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'tunai' => 'Tunai',
            'transfer' => 'Transfer Bank',
            'qris' => 'QRIS',
            default => $this->payment_method,
        };
    }

    /**
     * Helper method untuk mendapatkan jumlah bills dalam transaksi
     */
    public function getBillCountAttribute(): int
    {
        return $this->items()->count();
    }

    /**
     * Helper method untuk mendapatkan daftar student IDs dalam transaksi
     */
    public function getStudentIdsAttribute(): array
    {
        return $this->items()->pluck('student_id')->unique()->values()->toArray();
    }

    // ===================================================================
    // HELPER METHODS
    // ===================================================================

    /**
     * Helper method untuk check apakah transaksi bisa diverifikasi
     */
    public function canBeVerified(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Helper method untuk check apakah transaksi bisa dibatalkan
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'verified']);
    }

    /**
     * Verify transaksi dan semua payment items
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
     * Cancel transaksi dan semua payment items
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
     * Update status semua bills yang terkait dengan transaksi ini
     * Dipanggil setelah status transaksi berubah
     */
    public function updateAllBillStatuses(): void
    {
        $this->items->each(function ($item) {
            $item->bill->updatePaymentStatusFromTransaction();
        });
    }

    /**
     * Recalculate total amount dari semua items
     */
    public function recalculateTotal(): void
    {
        $this->total_amount = $this->items()->sum('amount');
        $this->saveQuietly(); // Tidak trigger events
    }

    /**
     * Generate nomor transaksi otomatis
     * Format: TRX/YYYY/MM/#####
     */
    public static function generateTransactionNumber(): string
    {
        $prefix = 'TRX';
        $year = now()->format('Y');
        $month = now()->format('m');

        $lastTransaction = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastTransaction
            ? ((int) substr($lastTransaction->transaction_number, -5)) + 1
            : 1;

        return sprintf('%s/%s/%s/%05d', $prefix, $year, $month, $sequence);
    }

    /**
     * Get summary untuk display di list
     */
    public function getSummaryAttribute(): array
    {
        return [
            'transaction_number' => $this->transaction_number,
            'total_amount' => $this->total_amount,
            'formatted_amount' => $this->formatted_amount,
            'payment_date' => $this->payment_date->format('d/m/Y'),
            'payment_method' => $this->payment_method,
            'method_label' => $this->method_label,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'bill_count' => $this->bill_count,
        ];
    }
}
