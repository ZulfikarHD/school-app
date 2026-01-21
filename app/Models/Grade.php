<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    /** @use HasFactory<\Database\Factories\GradeFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Konstanta untuk jenis assessment yang tersedia sesuai K13
     */
    public const ASSESSMENT_UH = 'UH';

    public const ASSESSMENT_UTS = 'UTS';

    public const ASSESSMENT_UAS = 'UAS';

    public const ASSESSMENT_PRAKTIK = 'PRAKTIK';

    public const ASSESSMENT_PROYEK = 'PROYEK';

    /**
     * Range nilai untuk predikat sesuai standar K13
     */
    public const PREDIKAT_RANGES = [
        'A' => ['min' => 90, 'max' => 100, 'label' => 'Sangat Baik'],
        'B' => ['min' => 80, 'max' => 89.99, 'label' => 'Baik'],
        'C' => ['min' => 70, 'max' => 79.99, 'label' => 'Cukup'],
        'D' => ['min' => 0, 'max' => 69.99, 'label' => 'Kurang'],
    ];

    /**
     * Mass assignable attributes untuk grade data yang mencakup
     * data siswa, penilaian, dan status lock
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'subject_id',
        'class_id',
        'teacher_id',
        'tahun_ajaran',
        'semester',
        'assessment_type',
        'assessment_number',
        'title',
        'assessment_date',
        'score',
        'notes',
        'is_locked',
        'locked_at',
        'locked_by',
    ];

    /**
     * Cast attributes ke tipe data yang sesuai untuk handling
     * date, boolean, dan decimal
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'assessment_date' => 'date',
            'score' => 'decimal:2',
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
        ];
    }

    /**
     * Relationship many-to-one dengan Student
     * dimana satu grade dimiliki oleh satu siswa
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relationship many-to-one dengan Subject
     * dimana satu grade terkait dengan satu mata pelajaran
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relationship many-to-one dengan SchoolClass
     * dimana satu grade terkait dengan satu kelas
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Relationship many-to-one dengan User (Teacher)
     * dimana satu grade diinput oleh satu guru
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Relationship many-to-one dengan User yang mengunci nilai
     */
    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Scope query untuk filter berdasarkan tahun ajaran
     *
     * @param  Builder<Grade>  $query
     * @return Builder<Grade>
     */
    public function scopeByTahunAjaran(Builder $query, string $tahunAjaran): Builder
    {
        return $query->where('tahun_ajaran', $tahunAjaran);
    }

    /**
     * Scope query untuk filter berdasarkan semester
     *
     * @param  Builder<Grade>  $query
     * @return Builder<Grade>
     */
    public function scopeBySemester(Builder $query, string $semester): Builder
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope query untuk filter berdasarkan jenis assessment
     *
     * @param  Builder<Grade>  $query
     * @return Builder<Grade>
     */
    public function scopeByAssessmentType(Builder $query, string $type): Builder
    {
        return $query->where('assessment_type', $type);
    }

    /**
     * Scope query untuk filter berdasarkan siswa
     *
     * @param  Builder<Grade>  $query
     * @return Builder<Grade>
     */
    public function scopeByStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope query untuk filter berdasarkan kelas
     *
     * @param  Builder<Grade>  $query
     * @return Builder<Grade>
     */
    public function scopeByClass(Builder $query, int $classId): Builder
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope query untuk filter berdasarkan mata pelajaran
     *
     * @param  Builder<Grade>  $query
     * @return Builder<Grade>
     */
    public function scopeBySubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope query untuk filter berdasarkan guru
     *
     * @param  Builder<Grade>  $query
     * @return Builder<Grade>
     */
    public function scopeByTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope query untuk filter nilai yang belum dikunci
     *
     * @param  Builder<Grade>  $query
     * @return Builder<Grade>
     */
    public function scopeUnlocked(Builder $query): Builder
    {
        return $query->where('is_locked', false);
    }

    /**
     * Scope query untuk filter nilai yang sudah dikunci
     *
     * @param  Builder<Grade>  $query
     * @return Builder<Grade>
     */
    public function scopeLocked(Builder $query): Builder
    {
        return $query->where('is_locked', true);
    }

    /**
     * Accessor untuk mendapatkan predikat berdasarkan nilai
     * A = 90-100 (Sangat Baik)
     * B = 80-89 (Baik)
     * C = 70-79 (Cukup)
     * D = <70 (Kurang)
     */
    public function getPredikatAttribute(): string
    {
        return self::getPredikatFromScore($this->score);
    }

    /**
     * Accessor untuk mendapatkan label predikat (Sangat Baik, Baik, dll)
     */
    public function getPredikatLabelAttribute(): string
    {
        $predikat = $this->predikat;

        return self::PREDIKAT_RANGES[$predikat]['label'] ?? 'Tidak Diketahui';
    }

    /**
     * Static helper untuk menghitung predikat dari score
     */
    public static function getPredikatFromScore(float $score): string
    {
        foreach (self::PREDIKAT_RANGES as $predikat => $range) {
            if ($score >= $range['min'] && $score <= $range['max']) {
                return $predikat;
            }
        }

        return 'D';
    }

    /**
     * Helper method untuk check apakah nilai sudah dikunci
     */
    public function isLocked(): bool
    {
        return $this->is_locked;
    }

    /**
     * Helper method untuk mengunci nilai
     */
    public function lock(int $userId): bool
    {
        if ($this->is_locked) {
            return false;
        }

        return $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $userId,
        ]);
    }

    /**
     * Helper method untuk membuka kunci nilai (hanya admin)
     */
    public function unlock(): bool
    {
        return $this->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
        ]);
    }

    /**
     * Static helper untuk mendapatkan list jenis assessment
     *
     * @return array<string, string>
     */
    public static function getAssessmentTypes(): array
    {
        return [
            self::ASSESSMENT_UH => 'Ulangan Harian',
            self::ASSESSMENT_UTS => 'Ujian Tengah Semester',
            self::ASSESSMENT_UAS => 'Ujian Akhir Semester',
            self::ASSESSMENT_PRAKTIK => 'Praktik',
            self::ASSESSMENT_PROYEK => 'Proyek',
        ];
    }
}
