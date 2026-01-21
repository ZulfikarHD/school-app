<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model PaymentReminderLog untuk tracking pengiriman reminder pembayaran
 *
 * Menyimpan log setiap reminder yang dikirim ke orang tua siswa,
 * mencakup tipe reminder (H-5, jatuh tempo, H+7), channel (WhatsApp/Email),
 * dan status pengiriman (pending, sent, failed)
 */
class PaymentReminderLog extends Model
{
    /**
     * Mass assignable attributes untuk reminder log
     *
     * @var list<string>
     */
    protected $fillable = [
        'bill_id',
        'reminder_type',
        'channel',
        'recipient',
        'message',
        'status',
        'sent_at',
        'error_message',
    ];

    /**
     * Cast attributes ke tipe data yang sesuai
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Konstanta untuk tipe reminder
     */
    public const TYPE_H_MINUS_5 = 'h_minus_5';

    public const TYPE_DUE_DATE = 'due_date';

    public const TYPE_H_PLUS_7 = 'h_plus_7';

    /**
     * Konstanta untuk channel pengiriman
     */
    public const CHANNEL_WHATSAPP = 'whatsapp';

    public const CHANNEL_EMAIL = 'email';

    /**
     * Konstanta untuk status pengiriman
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    /**
     * Relationship many-to-one dengan Bill
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Scope query untuk filter by reminder type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('reminder_type', $type);
    }

    /**
     * Scope query untuk filter by channel
     */
    public function scopeViaChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope query untuk filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope query untuk reminder yang berhasil terkirim
     */
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    /**
     * Scope query untuk reminder yang gagal
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope query untuk reminder yang pending
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Helper method untuk mendapatkan label tipe reminder
     */
    public function getReminderTypeLabelAttribute(): string
    {
        return match ($this->reminder_type) {
            self::TYPE_H_MINUS_5 => 'H-5 (5 Hari Sebelum Jatuh Tempo)',
            self::TYPE_DUE_DATE => 'Jatuh Tempo',
            self::TYPE_H_PLUS_7 => 'H+7 (7 Hari Setelah Jatuh Tempo)',
            default => $this->reminder_type,
        };
    }

    /**
     * Helper method untuk mendapatkan label channel
     */
    public function getChannelLabelAttribute(): string
    {
        return match ($this->channel) {
            self::CHANNEL_WHATSAPP => 'WhatsApp',
            self::CHANNEL_EMAIL => 'Email',
            default => $this->channel,
        };
    }

    /**
     * Helper method untuk mendapatkan label status
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_SENT => 'Terkirim',
            self::STATUS_FAILED => 'Gagal',
            default => $this->status,
        };
    }

    /**
     * Mark reminder sebagai terkirim
     */
    public function markAsSent(): bool
    {
        $this->status = self::STATUS_SENT;
        $this->sent_at = now();

        return $this->save();
    }

    /**
     * Mark reminder sebagai gagal dengan error message
     */
    public function markAsFailed(string $errorMessage): bool
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $errorMessage;

        return $this->save();
    }

    /**
     * Check apakah reminder untuk bill dan tipe tertentu sudah pernah dikirim
     */
    public static function hasBeenSent(int $billId, string $reminderType): bool
    {
        return self::query()
            ->where('bill_id', $billId)
            ->where('reminder_type', $reminderType)
            ->where('status', self::STATUS_SENT)
            ->exists();
    }
}
