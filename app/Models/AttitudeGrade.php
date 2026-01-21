<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttitudeGrade extends Model
{
    /** @use HasFactory<\Database\Factories\AttitudeGradeFactory> */
    use HasFactory;

    /**
     * Konstanta untuk predikat nilai sikap sesuai K13
     */
    public const GRADE_A = 'A';

    public const GRADE_B = 'B';

    public const GRADE_C = 'C';

    public const GRADE_D = 'D';

    /**
     * Label dan deskripsi untuk setiap predikat sikap
     */
    public const GRADE_LABELS = [
        'A' => 'Sangat Baik',
        'B' => 'Baik',
        'C' => 'Cukup',
        'D' => 'Kurang',
    ];

    /**
     * Mass assignable attributes untuk attitude grade data yang mencakup
     * nilai spiritual, sosial, dan catatan wali kelas
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_id',
        'class_id',
        'teacher_id',
        'tahun_ajaran',
        'semester',
        'spiritual_grade',
        'spiritual_description',
        'social_grade',
        'social_description',
        'homeroom_notes',
    ];

    /**
     * Relationship many-to-one dengan Student
     * dimana satu attitude grade dimiliki oleh satu siswa
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relationship many-to-one dengan SchoolClass
     * dimana satu attitude grade terkait dengan satu kelas
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Relationship many-to-one dengan User (Wali Kelas)
     * dimana satu attitude grade diinput oleh satu wali kelas
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Scope query untuk filter berdasarkan tahun ajaran
     *
     * @param  Builder<AttitudeGrade>  $query
     * @return Builder<AttitudeGrade>
     */
    public function scopeByTahunAjaran(Builder $query, string $tahunAjaran): Builder
    {
        return $query->where('tahun_ajaran', $tahunAjaran);
    }

    /**
     * Scope query untuk filter berdasarkan semester
     *
     * @param  Builder<AttitudeGrade>  $query
     * @return Builder<AttitudeGrade>
     */
    public function scopeBySemester(Builder $query, string $semester): Builder
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope query untuk filter berdasarkan kelas
     *
     * @param  Builder<AttitudeGrade>  $query
     * @return Builder<AttitudeGrade>
     */
    public function scopeByClass(Builder $query, int $classId): Builder
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope query untuk filter berdasarkan siswa
     *
     * @param  Builder<AttitudeGrade>  $query
     * @return Builder<AttitudeGrade>
     */
    public function scopeByStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope query untuk filter berdasarkan wali kelas
     *
     * @param  Builder<AttitudeGrade>  $query
     * @return Builder<AttitudeGrade>
     */
    public function scopeByTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Accessor untuk mendapatkan label spiritual grade
     */
    public function getSpiritualGradeLabelAttribute(): string
    {
        return self::GRADE_LABELS[$this->spiritual_grade] ?? 'Tidak Diketahui';
    }

    /**
     * Accessor untuk mendapatkan label social grade
     */
    public function getSocialGradeLabelAttribute(): string
    {
        return self::GRADE_LABELS[$this->social_grade] ?? 'Tidak Diketahui';
    }

    /**
     * Static helper untuk mendapatkan list predikat dengan label
     *
     * @return array<string, string>
     */
    public static function getGradeOptions(): array
    {
        return self::GRADE_LABELS;
    }

    /**
     * Static helper untuk mendapatkan template deskripsi sikap spiritual
     *
     * @return array<string, string>
     */
    public static function getSpiritualDescriptionTemplates(): array
    {
        return [
            'A' => 'Siswa selalu menunjukkan perilaku beriman dan bertakwa kepada Tuhan Yang Maha Esa dengan sangat baik.',
            'B' => 'Siswa menunjukkan perilaku beriman dan bertakwa kepada Tuhan Yang Maha Esa dengan baik.',
            'C' => 'Siswa cukup menunjukkan perilaku beriman dan bertakwa kepada Tuhan Yang Maha Esa.',
            'D' => 'Siswa perlu bimbingan lebih lanjut dalam menunjukkan perilaku beriman dan bertakwa.',
        ];
    }

    /**
     * Static helper untuk mendapatkan template deskripsi sikap sosial
     *
     * @return array<string, string>
     */
    public static function getSocialDescriptionTemplates(): array
    {
        return [
            'A' => 'Siswa selalu menunjukkan perilaku jujur, disiplin, tanggung jawab, santun, peduli, dan percaya diri dengan sangat baik.',
            'B' => 'Siswa menunjukkan perilaku jujur, disiplin, tanggung jawab, santun, peduli, dan percaya diri dengan baik.',
            'C' => 'Siswa cukup menunjukkan perilaku jujur, disiplin, tanggung jawab, santun, peduli, dan percaya diri.',
            'D' => 'Siswa perlu bimbingan lebih lanjut dalam perilaku jujur, disiplin, tanggung jawab, santun, peduli, dan percaya diri.',
        ];
    }
}
