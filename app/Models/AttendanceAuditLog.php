<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_type',
        'attendance_id',
        'user_id',
        'field_changed',
        'old_value',
        'new_value',
        'reason',
    ];

    /**
     * Relasi ke user yang melakukan perubahan
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log perubahan attendance
     */
    public static function logChange(
        string $attendanceType,
        int $attendanceId,
        int $userId,
        string $fieldChanged,
        ?string $oldValue,
        ?string $newValue,
        ?string $reason = null
    ): self {
        return self::create([
            'attendance_type' => $attendanceType,
            'attendance_id' => $attendanceId,
            'user_id' => $userId,
            'field_changed' => $fieldChanged,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'reason' => $reason,
        ]);
    }
}
