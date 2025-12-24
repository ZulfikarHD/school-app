<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    /** @use HasFactory<\Database\Factories\LeaveRequestFactory> */
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara mass assignment
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'jenis',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'attachment_path',
        'status',
        'submitted_by',
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
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke siswa yang mengajukan izin
     *
     * @return BelongsTo<Student, LeaveRequest>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relasi ke orang tua yang submit permohonan
     *
     * @return BelongsTo<User, LeaveRequest>
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Relasi ke guru/admin yang review permohonan
     *
     * @return BelongsTo<User, LeaveRequest>
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Approve permohonan izin dan sync ke student_attendances
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
     * Reject permohonan izin dengan alasan tertentu
     * untuk memberikan feedback ke orang tua
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

    /**
     * Scope untuk filter permohonan yang pending
     *
     * @param  Builder<LeaveRequest>  $query
     * @return Builder<LeaveRequest>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * Scope untuk filter permohonan yang approved
     *
     * @param  Builder<LeaveRequest>  $query
     * @return Builder<LeaveRequest>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'APPROVED');
    }

    /**
     * Scope untuk filter permohonan yang rejected
     *
     * @param  Builder<LeaveRequest>  $query
     * @return Builder<LeaveRequest>
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'REJECTED');
    }
}
