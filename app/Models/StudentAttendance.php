<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendance extends Model
{
    /** @use HasFactory<\Database\Factories\StudentAttendanceFactory> */
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara mass assignment
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'class_id',
        'tanggal',
        'status',
        'keterangan',
        'recorded_by',
        'recorded_at',
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
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke siswa yang diabsen
     *
     * @return BelongsTo<Student, StudentAttendance>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relasi ke kelas siswa
     *
     * @return BelongsTo<SchoolClass, StudentAttendance>
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Relasi ke guru yang menginput data
     *
     * @return BelongsTo<User, StudentAttendance>
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Scope untuk filter berdasarkan tanggal
     *
     * @param  Builder<StudentAttendance>  $query
     * @return Builder<StudentAttendance>
     */
    public function scopeByDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('tanggal', $date);
    }

    /**
     * Scope untuk filter berdasarkan range tanggal
     *
     * @param  Builder<StudentAttendance>  $query
     * @return Builder<StudentAttendance>
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    /**
     * Scope untuk filter berdasarkan status
     *
     * @param  Builder<StudentAttendance>  $query
     * @return Builder<StudentAttendance>
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter siswa yang hadir
     *
     * @param  Builder<StudentAttendance>  $query
     * @return Builder<StudentAttendance>
     */
    public function scopeHadir(Builder $query): Builder
    {
        return $query->where('status', 'H');
    }

    /**
     * Scope untuk filter siswa yang alpha
     *
     * @param  Builder<StudentAttendance>  $query
     * @return Builder<StudentAttendance>
     */
    public function scopeAlpha(Builder $query): Builder
    {
        return $query->where('status', 'A');
    }

    /**
     * Accessor untuk mendapatkan label status yang terformat
     * yaitu: H = Hadir, I = Izin, S = Sakit, A = Alpha
     *
     * @return Attribute<string, never>
     */
    protected function formattedStatus(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                'H' => 'Hadir',
                'I' => 'Izin',
                'S' => 'Sakit',
                'A' => 'Alpha',
                default => 'Unknown',
            },
        );
    }
}
