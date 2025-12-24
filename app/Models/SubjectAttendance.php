<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectAttendance extends Model
{
    /** @use HasFactory<\Database\Factories\SubjectAttendanceFactory> */
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara mass assignment
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'class_id',
        'subject_id',
        'teacher_id',
        'tanggal',
        'jam_ke',
        'status',
        'keterangan',
    ];

    /**
     * Cast atribut ke tipe data native
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam_ke' => 'integer',
        ];
    }

    /**
     * Relasi ke siswa yang diabsen
     *
     * @return BelongsTo<Student, SubjectAttendance>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relasi ke kelas
     *
     * @return BelongsTo<SchoolClass, SubjectAttendance>
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Relasi ke mata pelajaran
     *
     * @return BelongsTo<Subject, SubjectAttendance>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relasi ke guru pengajar
     *
     * @return BelongsTo<User, SubjectAttendance>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Scope untuk filter berdasarkan mata pelajaran
     *
     * @param  Builder<SubjectAttendance>  $query
     * @return Builder<SubjectAttendance>
     */
    public function scopeBySubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope untuk filter berdasarkan guru
     *
     * @param  Builder<SubjectAttendance>  $query
     * @return Builder<SubjectAttendance>
     */
    public function scopeByTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     *
     * @param  Builder<SubjectAttendance>  $query
     * @return Builder<SubjectAttendance>
     */
    public function scopeByDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('tanggal', $date);
    }
}
