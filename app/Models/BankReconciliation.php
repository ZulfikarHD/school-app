<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model BankReconciliation untuk mengelola rekonsiliasi bank
 *
 * Menyimpan data header rekonsiliasi termasuk informasi file statement,
 * summary hasil matching, dan status verifikasi
 */
class BankReconciliation extends Model
{
    /**
     * Mass assignable attributes
     *
     * @var list<string>
     */
    protected $fillable = [
        'filename',
        'original_filename',
        'bank_name',
        'statement_date',
        'statement_start_date',
        'statement_end_date',
        'total_transactions',
        'total_amount',
        'matched_count',
        'matched_amount',
        'unmatched_count',
        'status',
        'uploaded_by',
        'verified_by',
        'verified_at',
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
            'statement_date' => 'date',
            'statement_start_date' => 'date',
            'statement_end_date' => 'date',
            'total_amount' => 'decimal:2',
            'matched_amount' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Konstanta untuk status rekonsiliasi
     */
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_VERIFIED = 'verified';

    /**
     * Relationship one-to-many dengan BankReconciliationItem
     */
    public function items(): HasMany
    {
        return $this->hasMany(BankReconciliationItem::class, 'reconciliation_id');
    }

    /**
     * Relationship many-to-one dengan User (uploader)
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Relationship many-to-one dengan User (verifier)
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope query untuk filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope query untuk draft
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope query untuk completed
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope query untuk verified
     */
    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    /**
     * Get items yang sudah di-match
     */
    public function matchedItems(): HasMany
    {
        return $this->items()->whereIn('match_type', ['auto', 'manual']);
    }

    /**
     * Get items yang belum di-match
     */
    public function unmatchedItems(): HasMany
    {
        return $this->items()->where('match_type', 'unmatched');
    }

    /**
     * Helper method untuk format total amount ke Rupiah
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp '.number_format($this->total_amount, 0, ',', '.');
    }

    /**
     * Helper method untuk format matched amount ke Rupiah
     */
    public function getFormattedMatchedAmountAttribute(): string
    {
        return 'Rp '.number_format($this->matched_amount, 0, ',', '.');
    }

    /**
     * Helper method untuk mendapatkan label status
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PROCESSING => 'Sedang Diproses',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_VERIFIED => 'Terverifikasi',
            default => $this->status,
        };
    }

    /**
     * Helper method untuk mendapatkan match rate percentage
     */
    public function getMatchRateAttribute(): float
    {
        if ($this->total_transactions === 0) {
            return 0;
        }

        return round(($this->matched_count / $this->total_transactions) * 100, 1);
    }

    /**
     * Check apakah rekonsiliasi bisa diverifikasi
     */
    public function canBeVerified(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verify rekonsiliasi
     */
    public function verify(?int $userId = null): bool
    {
        if (! $this->canBeVerified()) {
            return false;
        }

        $this->status = self::STATUS_VERIFIED;
        $this->verified_by = $userId ?? auth()->id();
        $this->verified_at = now();

        return $this->save();
    }

    /**
     * Update statistics setelah matching
     */
    public function updateStatistics(): void
    {
        $items = $this->items()->get();

        $this->total_transactions = $items->count();
        $this->total_amount = $items->sum('amount');

        $matched = $items->whereIn('match_type', ['auto', 'manual']);
        $this->matched_count = $matched->count();
        $this->matched_amount = $matched->sum('amount');

        $this->unmatched_count = $items->where('match_type', 'unmatched')->count();

        // Update status based on matching progress
        if ($this->unmatched_count === 0 && $this->total_transactions > 0) {
            $this->status = self::STATUS_COMPLETED;
        } elseif ($this->matched_count > 0) {
            $this->status = self::STATUS_PROCESSING;
        }

        $this->save();
    }
}
