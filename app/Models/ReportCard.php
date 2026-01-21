<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCard extends Model
{
    /** @use HasFactory<\Database\Factories\ReportCardFactory> */
    use HasFactory;

    /**
     * Konstanta untuk status rapor dalam approval flow
     */
    public const STATUS_DRAFT = 'DRAFT';

    public const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_RELEASED = 'RELEASED';

    /**
     * Label untuk setiap status rapor
     */
    public const STATUS_LABELS = [
        'DRAFT' => 'Draft',
        'PENDING_APPROVAL' => 'Menunggu Persetujuan',
        'APPROVED' => 'Disetujui',
        'RELEASED' => 'Dirilis',
    ];

    /**
     * Warna badge untuk setiap status
     */
    public const STATUS_COLORS = [
        'DRAFT' => 'gray',
        'PENDING_APPROVAL' => 'yellow',
        'APPROVED' => 'blue',
        'RELEASED' => 'green',
    ];

    /**
     * Mass assignable attributes untuk report card data yang mencakup
     * data siswa, status approval, dan summary nilai
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'class_id',
        'tahun_ajaran',
        'semester',
        'status',
        'generated_at',
        'generated_by',
        'approved_at',
        'approved_by',
        'approval_notes',
        'released_at',
        'pdf_path',
        'average_score',
        'rank',
    ];

    /**
     * Cast attributes ke tipe data yang sesuai untuk handling
     * datetime dan decimal
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'approved_at' => 'datetime',
            'released_at' => 'datetime',
            'average_score' => 'decimal:2',
            'rank' => 'integer',
        ];
    }

    /**
     * Relationship many-to-one dengan Student
     * dimana satu report card dimiliki oleh satu siswa
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relationship many-to-one dengan SchoolClass
     * dimana satu report card terkait dengan satu kelas
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Relationship many-to-one dengan User yang men-generate rapor
     */
    public function generatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Relationship many-to-one dengan User (Principal) yang menyetujui rapor
     */
    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope query untuk filter berdasarkan tahun ajaran
     *
     * @param  Builder<ReportCard>  $query
     * @return Builder<ReportCard>
     */
    public function scopeByTahunAjaran(Builder $query, string $tahunAjaran): Builder
    {
        return $query->where('tahun_ajaran', $tahunAjaran);
    }

    /**
     * Scope query untuk filter berdasarkan semester
     *
     * @param  Builder<ReportCard>  $query
     * @return Builder<ReportCard>
     */
    public function scopeBySemester(Builder $query, string $semester): Builder
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope query untuk filter berdasarkan kelas
     *
     * @param  Builder<ReportCard>  $query
     * @return Builder<ReportCard>
     */
    public function scopeByClass(Builder $query, int $classId): Builder
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope query untuk filter berdasarkan status
     *
     * @param  Builder<ReportCard>  $query
     * @return Builder<ReportCard>
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope query untuk filter rapor yang sudah dirilis
     *
     * @param  Builder<ReportCard>  $query
     * @return Builder<ReportCard>
     */
    public function scopeReleased(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RELEASED);
    }

    /**
     * Scope query untuk filter rapor yang menunggu approval
     *
     * @param  Builder<ReportCard>  $query
     * @return Builder<ReportCard>
     */
    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_APPROVAL);
    }

    /**
     * Accessor untuk mendapatkan label status
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? 'Tidak Diketahui';
    }

    /**
     * Accessor untuk mendapatkan warna badge status
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    /**
     * Accessor untuk mendapatkan predikat dari rata-rata nilai
     */
    public function getPredikatAttribute(): string
    {
        if ($this->average_score === null) {
            return '-';
        }

        return Grade::getPredikatFromScore($this->average_score);
    }

    /**
     * Helper method untuk check apakah rapor sudah dirilis
     */
    public function isReleased(): bool
    {
        return $this->status === self::STATUS_RELEASED;
    }

    /**
     * Helper method untuk check apakah rapor bisa di-edit
     */
    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Helper method untuk check apakah rapor bisa di-approve
     */
    public function isApprovable(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    /**
     * Helper method untuk submit rapor untuk approval
     */
    public function submitForApproval(int $generatedBy): bool
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_PENDING_APPROVAL,
            'generated_at' => now(),
            'generated_by' => $generatedBy,
        ]);
    }

    /**
     * Helper method untuk approve rapor
     */
    public function approve(int $approvedBy, ?string $notes = null): bool
    {
        if ($this->status !== self::STATUS_PENDING_APPROVAL) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $approvedBy,
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Helper method untuk reject rapor (kembali ke draft)
     */
    public function reject(int $rejectedBy, string $notes): bool
    {
        if ($this->status !== self::STATUS_PENDING_APPROVAL) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_DRAFT,
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Helper method untuk release rapor ke parent
     */
    public function release(): bool
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_RELEASED,
            'released_at' => now(),
        ]);
    }

    /**
     * Static helper untuk mendapatkan list status dengan label
     *
     * @return array<string, string>
     */
    public static function getStatusOptions(): array
    {
        return self::STATUS_LABELS;
    }
}
