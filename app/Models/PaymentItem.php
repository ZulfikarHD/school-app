<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model PaymentItem untuk mengelola detail pembayaran per tagihan
 *
 * Setiap PaymentItem merepresentasikan pembayaran untuk satu Bill
 * dalam konteks satu PaymentTransaction. Mendukung multiple bills
 * dalam satu transaksi pembayaran.
 */
class PaymentItem extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes untuk item pembayaran
     *
     * @var list<string>
     */
    protected $fillable = [
        'payment_transaction_id',
        'bill_id',
        'student_id',
        'amount',
    ];

    /**
     * Cast attributes ke tipe data yang sesuai
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Boot method untuk update bill status saat item berubah
     */
    protected static function boot(): void
    {
        parent::boot();

        // Update bill status setelah item created
        static::created(function ($model) {
            // Recalculate parent transaction total
            $model->paymentTransaction->recalculateTotal();

            // Update bill status jika transaksi sudah verified
            if ($model->paymentTransaction->status === 'verified') {
                $model->bill->updatePaymentStatusFromTransaction();
            }
        });

        // Update bill status setelah item updated
        static::updated(function ($model) {
            // Recalculate parent transaction total
            $model->paymentTransaction->recalculateTotal();

            // Update bill status jika transaksi sudah verified
            if ($model->paymentTransaction->status === 'verified') {
                $model->bill->updatePaymentStatusFromTransaction();
            }
        });

        // Update bill status setelah item deleted
        static::deleted(function ($model) {
            // Recalculate parent transaction total
            $model->paymentTransaction->recalculateTotal();

            // Update bill status
            $model->bill->updatePaymentStatusFromTransaction();
        });
    }

    // ===================================================================
    // RELATIONSHIPS
    // ===================================================================

    /**
     * Relationship many-to-one dengan PaymentTransaction
     */
    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
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

    // ===================================================================
    // SCOPES
    // ===================================================================

    /**
     * Scope query untuk filter berdasarkan status transaksi parent
     */
    public function scopeVerified($query)
    {
        return $query->whereHas('paymentTransaction', function ($q) {
            $q->where('status', 'verified');
        });
    }

    /**
     * Scope query untuk filter berdasarkan student
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope query untuk filter berdasarkan bill
     */
    public function scopeForBill($query, int $billId)
    {
        return $query->where('bill_id', $billId);
    }

    // ===================================================================
    // ACCESSORS
    // ===================================================================

    /**
     * Helper method untuk format amount ke Rupiah
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp '.number_format($this->amount, 0, ',', '.');
    }

    /**
     * Cek apakah item ini sudah verified (berdasarkan parent transaction)
     */
    public function getIsVerifiedAttribute(): bool
    {
        return $this->paymentTransaction->status === 'verified';
    }

    /**
     * Cek apakah item ini pending (berdasarkan parent transaction)
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->paymentTransaction->status === 'pending';
    }

    /**
     * Cek apakah item ini cancelled (berdasarkan parent transaction)
     */
    public function getIsCancelledAttribute(): bool
    {
        return $this->paymentTransaction->status === 'cancelled';
    }

    // ===================================================================
    // HELPER METHODS
    // ===================================================================

    /**
     * Get summary untuk display
     */
    public function getSummaryAttribute(): array
    {
        return [
            'bill_number' => $this->bill->nomor_tagihan,
            'bill_type' => $this->bill->paymentCategory->nama ?? '-',
            'period' => $this->bill->nama_bulan.' '.$this->bill->tahun,
            'amount' => $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'student_name' => $this->student->nama_lengkap ?? '-',
        ];
    }
}
