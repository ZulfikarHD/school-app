<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    /** @use HasFactory<\Database\Factories\SubjectFactory> */
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara mass assignment
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'tingkat',
        'kategori',
        'is_active',
    ];

    /**
     * Cast atribut ke tipe data native
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tingkat' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke guru yang mengajar mata pelajaran ini
     * melalui pivot table teacher_subjects
     *
     * @return BelongsToMany<Teacher>
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subjects', 'subject_id', 'teacher_id')
            ->withPivot('class_id', 'tahun_ajaran', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Relasi untuk mendapatkan guru yang mengajar mapel ini sebagai mata pelajaran utama
     *
     * @return BelongsToMany<Teacher>
     */
    public function primaryTeachers(): BelongsToMany
    {
        return $this->teachers()->wherePivot('is_primary', true);
    }

    /**
     * Relasi ke absensi per mata pelajaran
     *
     * @return HasMany<SubjectAttendance>
     */
    public function subjectAttendances(): HasMany
    {
        return $this->hasMany(SubjectAttendance::class);
    }

    /**
     * Relasi ke kelas yang menggunakan mata pelajaran ini
     * melalui pivot table teacher_subjects
     *
     * @return BelongsToMany<SchoolClass>
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_subjects', 'subject_id', 'class_id')
            ->withPivot('teacher_id', 'tahun_ajaran')
            ->withTimestamps();
    }

    /**
     * Scope untuk filter mata pelajaran yang aktif
     *
     * @param  Builder<Subject>  $query
     * @return Builder<Subject>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter berdasarkan tingkat kelas
     *
     * @param  Builder<Subject>  $query
     * @return Builder<Subject>
     */
    public function scopeByLevel(Builder $query, ?int $tingkat): Builder
    {
        return $query->where(function ($q) use ($tingkat) {
            $q->whereNull('tingkat')
                ->orWhere('tingkat', $tingkat);
        });
    }

    /**
     * Scope untuk filter berdasarkan kategori
     *
     * @param  Builder<Subject>  $query
     * @return Builder<Subject>
     */
    public function scopeByCategory(Builder $query, string $kategori): Builder
    {
        return $query->where('kategori', $kategori);
    }
}
