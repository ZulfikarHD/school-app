<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentStatusHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'student_status_history';

    /**
     * Mass assignable attributes untuk riwayat perubahan status siswa
     * yang mencakup status lama/baru, tanggal, alasan, dan keterangan
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'status_lama',
        'status_baru',
        'tanggal',
        'alasan',
        'keterangan',
        'sekolah_tujuan',
        'changed_by',
    ];

    /**
     * Cast attributes ke tipe data yang sesuai
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    /**
     * Relationship dengan Student untuk link history ke siswa tertentu
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relationship dengan User untuk tracking siapa yang melakukan perubahan status
     * sebagai bagian dari audit trail
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Helper method untuk mendapatkan label status yang lebih readable
     */
    public function getStatusBaruLabelAttribute(): string
    {
        return match ($this->status_baru) {
            'aktif' => 'Aktif',
            'mutasi' => 'Mutasi',
            'do' => 'Drop Out (DO)',
            'lulus' => 'Lulus',
            default => $this->status_baru,
        };
    }

    /**
     * Scope query untuk filter history berdasarkan status baru
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_baru', $status);
    }

    /**
     * Scope query untuk filter history berdasarkan tanggal
     */
    public function scopeByDate($query, $tanggal)
    {
        return $query->whereDate('tanggal', $tanggal);
    }
}
