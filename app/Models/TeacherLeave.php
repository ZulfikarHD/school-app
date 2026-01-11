<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherLeave extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherLeaveFactory> */
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara mass assignment
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'teacher_id',
        'jenis',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'alasan',
        'attachment_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    /**
     * Cast atribut ke tipe data native
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'jumlah_hari' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Append accessors untuk serialization
     * agar frontend bisa mengakses dengan nama English
     *
     * @var array<int, string>
     */
    protected $appends = [
        'start_date',
        'end_date',
        'type',
        'reason',
        'approved_at',
    ];

    /**
     * Accessor untuk start_date (alias untuk tanggal_mulai)
     * untuk konsistensi dengan frontend
     */
    public function getStartDateAttribute(): ?string
    {
        return $this->tanggal_mulai?->format('Y-m-d');
    }

    /**
     * Accessor untuk end_date (alias untuk tanggal_selesai)
     * untuk konsistensi dengan frontend
     */
    public function getEndDateAttribute(): ?string
    {
        return $this->tanggal_selesai?->format('Y-m-d');
    }

    /**
     * Accessor untuk type (alias untuk jenis)
     * untuk konsistensi dengan frontend
     */
    public function getTypeAttribute(): ?string
    {
        return $this->jenis;
    }

    /**
     * Accessor untuk reason (alias untuk alasan)
     * untuk konsistensi dengan frontend
     */
    public function getReasonAttribute(): ?string
    {
        return $this->alasan;
    }

    /**
     * Accessor untuk approved_at (alias untuk reviewed_at)
     * untuk konsistensi dengan frontend
     */
    public function getApprovedAtAttribute(): ?string
    {
        return $this->reviewed_at?->format('Y-m-d H:i:s');
    }

    /**
     * Relasi ke guru yang mengajukan cuti
     *
     * @return BelongsTo<User, TeacherLeave>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Relasi ke admin/principal yang review permohonan
     *
     * @return BelongsTo<User, TeacherLeave>
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Alias untuk reviewedBy - untuk backward compatibility
     *
     * @return BelongsTo<User, TeacherLeave>
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Approve permohonan cuti guru
     * dengan mengubah status menjadi APPROVED
     */
    public function approve(User $reviewer): void
    {
        $this->update([
            'status' => 'APPROVED',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Reject permohonan cuti guru dengan alasan tertentu
     * untuk memberikan feedback ke guru
     */
    public function reject(User $reviewer, string $reason): void
    {
        $this->update([
            'status' => 'REJECTED',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
