<?php

namespace App\Models;

use App\Enums\Hari;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * TeachingSchedule Model - Representasi jadwal mengajar guru
 *
 * Model ini bertujuan untuk menyimpan dan mengelola data jadwal mengajar
 * yang mencakup: guru, mata pelajaran, kelas, hari, waktu, dan ruangan
 *
 * @property int $id
 * @property int $teacher_id
 * @property int $subject_id
 * @property int $class_id
 * @property int $academic_year_id
 * @property Hari $hari
 * @property string $jam_mulai
 * @property string $jam_selesai
 * @property string|null $ruangan
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class TeachingSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\TeachingScheduleFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes untuk teaching schedule data
     * yang mencakup referensi guru, mapel, kelas, dan waktu
     *
     * @var list<string>
     */
    protected $fillable = [
        'teacher_id',
        'subject_id',
        'class_id',
        'academic_year_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'ruangan',
        'is_active',
    ];

    /**
     * Cast attributes ke tipe data yang sesuai untuk handling enum dan boolean
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hari' => Hari::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relationship many-to-one dengan Teacher
     * dimana satu schedule dimiliki oleh satu guru
     *
     * @return BelongsTo<Teacher, TeachingSchedule>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Relationship many-to-one dengan Subject
     * dimana satu schedule untuk satu mata pelajaran
     *
     * @return BelongsTo<Subject, TeachingSchedule>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relationship many-to-one dengan SchoolClass
     * dimana satu schedule untuk satu kelas
     *
     * @return BelongsTo<SchoolClass, TeachingSchedule>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Relationship many-to-one dengan AcademicYear
     * dimana satu schedule berlaku untuk satu tahun ajaran
     *
     * @return BelongsTo<AcademicYear, TeachingSchedule>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Scope query untuk filter schedule berdasarkan teacher
     *
     * @param  Builder<TeachingSchedule>  $query
     * @return Builder<TeachingSchedule>
     */
    public function scopeByTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope query untuk filter schedule berdasarkan class
     *
     * @param  Builder<TeachingSchedule>  $query
     * @return Builder<TeachingSchedule>
     */
    public function scopeByClass(Builder $query, int $classId): Builder
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope query untuk filter schedule berdasarkan hari
     *
     * @param  Builder<TeachingSchedule>  $query
     * @return Builder<TeachingSchedule>
     */
    public function scopeByDay(Builder $query, string|Hari $hari): Builder
    {
        $hariValue = $hari instanceof Hari ? $hari->value : $hari;

        return $query->where('hari', $hariValue);
    }

    /**
     * Scope query untuk filter schedule berdasarkan academic year
     *
     * @param  Builder<TeachingSchedule>  $query
     * @return Builder<TeachingSchedule>
     */
    public function scopeByAcademicYear(Builder $query, int $academicYearId): Builder
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope query untuk filter schedule yang aktif
     *
     * @param  Builder<TeachingSchedule>  $query
     * @return Builder<TeachingSchedule>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query untuk mencari schedule yang overlap dengan waktu tertentu
     * Digunakan untuk conflict detection
     *
     * @param  Builder<TeachingSchedule>  $query
     * @param  string  $jamMulai  Format H:i
     * @param  string  $jamSelesai  Format H:i
     * @param  int|null  $excludeId  ID schedule yang dikecualikan (untuk update)
     * @return Builder<TeachingSchedule>
     */
    public function scopeHasTimeOverlap(Builder $query, string $jamMulai, string $jamSelesai, ?int $excludeId = null): Builder
    {
        return $query->where(function (Builder $q) use ($jamMulai, $jamSelesai) {
            // Case 1: Schedule baru dimulai di tengah schedule existing
            $q->where(function (Builder $q2) use ($jamMulai) {
                $q2->where('jam_mulai', '<=', $jamMulai)
                    ->where('jam_selesai', '>', $jamMulai);
            })
            // Case 2: Schedule baru selesai di tengah schedule existing
                ->orWhere(function (Builder $q2) use ($jamSelesai) {
                    $q2->where('jam_mulai', '<', $jamSelesai)
                        ->where('jam_selesai', '>=', $jamSelesai);
                })
            // Case 3: Schedule baru mencakup keseluruhan schedule existing
                ->orWhere(function (Builder $q2) use ($jamMulai, $jamSelesai) {
                    $q2->where('jam_mulai', '>=', $jamMulai)
                        ->where('jam_selesai', '<=', $jamSelesai);
                });
        })
            ->when($excludeId, fn (Builder $q) => $q->where('id', '!=', $excludeId));
    }

    /**
     * Scope query untuk search schedule berdasarkan nama guru atau mapel
     *
     * @param  Builder<TeachingSchedule>  $query
     * @return Builder<TeachingSchedule>
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->whereHas('teacher', function (Builder $q2) use ($search) {
                $q2->where('nama_lengkap', 'like', "%{$search}%");
            })
                ->orWhereHas('subject', function (Builder $q2) use ($search) {
                    $q2->where('nama_mapel', 'like', "%{$search}%")
                        ->orWhere('kode_mapel', 'like', "%{$search}%");
                })
                ->orWhereHas('schoolClass', function (Builder $q2) use ($search) {
                    $q2->where('nama', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Helper method untuk mendapatkan durasi jadwal dalam menit
     */
    public function getDurationInMinutes(): int
    {
        $start = \Carbon\Carbon::parse($this->jam_mulai);
        $end = \Carbon\Carbon::parse($this->jam_selesai);

        return $start->diffInMinutes($end);
    }

    /**
     * Helper method untuk mendapatkan durasi jadwal dalam jam pelajaran (45 menit)
     */
    public function getJamPelajaran(): float
    {
        return $this->getDurationInMinutes() / 45;
    }

    /**
     * Helper method untuk format waktu display
     */
    public function getTimeRangeAttribute(): string
    {
        $start = \Carbon\Carbon::parse($this->jam_mulai)->format('H:i');
        $end = \Carbon\Carbon::parse($this->jam_selesai)->format('H:i');

        return "{$start} - {$end}";
    }

    /**
     * Helper method untuk mendapatkan nama lengkap kelas (tingkat + nama)
     */
    public function getFullClassNameAttribute(): string
    {
        if (! $this->relationLoaded('schoolClass') || ! $this->schoolClass) {
            return '-';
        }

        return "Kelas {$this->schoolClass->tingkat}{$this->schoolClass->nama}";
    }
}
