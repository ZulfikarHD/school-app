<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model BankReconciliationItem untuk detail transaksi rekonsiliasi
 *
 * Menyimpan setiap baris transaksi dari statement bank
 * dan hasil matching dengan pembayaran di sistem
 */
class BankReconciliationItem extends Model
{
    /**
     * Mass assignable attributes
     *
     * @var list<string>
     */
    protected $fillable = [
        'reconciliation_id',
        'transaction_date',
        'description',
        'amount',
        'transaction_type',
        'reference',
        'payment_id',
        'match_type',
        'match_confidence',
        'matched_at',
        'matched_by',
        'notes',
    ];

    /**
     * Cast attributes ke tipe data yang sesuai
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
            'match_confidence' => 'decimal:2',
            'matched_at' => 'datetime',
        ];
    }

    /**
     * Konstanta untuk tipe transaksi
     */
    public const TYPE_CREDIT = 'credit';

    public const TYPE_DEBIT = 'debit';

    /**
     * Konstanta untuk tipe matching
     */
    public const MATCH_AUTO = 'auto';

    public const MATCH_MANUAL = 'manual';

    public const MATCH_UNMATCHED = 'unmatched';

    /**
     * Relationship many-to-one dengan BankReconciliation
     */
    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class, 'reconciliation_id');
    }

    /**
     * Relationship many-to-one dengan Payment
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Relationship many-to-one dengan User (matcher)
     */
    public function matcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_by');
    }

    /**
     * Scope query untuk filter by match type
     */
    public function scopeOfMatchType($query, string $type)
    {
        return $query->where('match_type', $type);
    }

    /**
     * Scope query untuk items yang sudah di-match
     */
    public function scopeMatched($query)
    {
        return $query->whereIn('match_type', [self::MATCH_AUTO, self::MATCH_MANUAL]);
    }

    /**
     * Scope query untuk items yang belum di-match
     */
    public function scopeUnmatched($query)
    {
        return $query->where('match_type', self::MATCH_UNMATCHED);
    }

    /**
     * Scope query untuk credit transactions (uang masuk)
     */
    public function scopeCredits($query)
    {
        return $query->where('transaction_type', self::TYPE_CREDIT);
    }

    /**
     * Scope query untuk debit transactions (uang keluar)
     */
    public function scopeDebits($query)
    {
        return $query->where('transaction_type', self::TYPE_DEBIT);
    }

    /**
     * Helper method untuk format amount ke Rupiah
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp '.number_format($this->amount, 0, ',', '.');
    }

    /**
     * Helper method untuk mendapatkan label match type
     */
    public function getMatchTypeLabelAttribute(): string
    {
        return match ($this->match_type) {
            self::MATCH_AUTO => 'Auto Match',
            self::MATCH_MANUAL => 'Manual Match',
            self::MATCH_UNMATCHED => 'Belum Di-match',
            default => $this->match_type,
        };
    }

    /**
     * Helper method untuk mendapatkan label transaction type
     */
    public function getTransactionTypeLabelAttribute(): string
    {
        return match ($this->transaction_type) {
            self::TYPE_CREDIT => 'Kredit (Masuk)',
            self::TYPE_DEBIT => 'Debit (Keluar)',
            default => $this->transaction_type,
        };
    }

    /**
     * Check apakah item sudah di-match
     */
    public function isMatched(): bool
    {
        return in_array($this->match_type, [self::MATCH_AUTO, self::MATCH_MANUAL]);
    }

    /**
     * Match item dengan payment (manual)
     */
    public function matchWithPayment(Payment $payment, ?int $userId = null): bool
    {
        $this->payment_id = $payment->id;
        $this->match_type = self::MATCH_MANUAL;
        $this->matched_at = now();
        $this->matched_by = $userId ?? auth()->id();

        $result = $this->save();

        // Update reconciliation statistics
        $this->reconciliation->updateStatistics();

        return $result;
    }

    /**
     * Auto-match item dengan payment
     */
    public function autoMatchWithPayment(Payment $payment, float $confidence): bool
    {
        $this->payment_id = $payment->id;
        $this->match_type = self::MATCH_AUTO;
        $this->match_confidence = $confidence;
        $this->matched_at = now();

        return $this->save();
    }

    /**
     * Unmatch item (remove payment link)
     */
    public function unmatch(): bool
    {
        $this->payment_id = null;
        $this->match_type = self::MATCH_UNMATCHED;
        $this->match_confidence = null;
        $this->matched_at = null;
        $this->matched_by = null;

        $result = $this->save();

        // Update reconciliation statistics
        $this->reconciliation->updateStatistics();

        return $result;
    }
}
