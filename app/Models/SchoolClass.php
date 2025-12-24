<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    /** @use HasFactory<\Database\Factories\SchoolClassFactory> */
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'tingkat',
        'nama',
        'wali_kelas_id',
        'kapasitas',
        'tahun_ajaran',
        'is_active',
    ];

    protected $casts = [
        'tingkat' => 'integer',
        'kapasitas' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Kelas memiliki banyak siswa
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'kelas_id');
    }

    /**
     * Relationship: Kelas memiliki satu wali kelas (Teacher)
     */
    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    /**
     * Relationship: Kelas memiliki banyak history perpindahan siswa
     */
    public function classHistory(): HasMany
    {
        return $this->hasMany(StudentClassHistory::class, 'kelas_id');
    }

    /**
     * Accessor untuk nama lengkap kelas (e.g., "1A")
     * Note: Ini sudah ada sebagai virtual column di DB, tapi kita define juga di sini
     * untuk handling jika data belum saved atau penggunaan in-memory
     */
    public function getNamaLengkapAttribute(): string
    {
        // Jika loaded from DB with virtual column, use it
        if (isset($this->attributes['nama_lengkap'])) {
            return $this->attributes['nama_lengkap'];
        }

        return $this->tingkat.$this->nama;
    }

    /**
     * Scope: Filter kelas aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter berdasarkan tahun ajaran
     */
    public function scopeByAcademicYear($query, $tahunAjaran)
    {
        return $query->where('tahun_ajaran', $tahunAjaran);
    }

    /**
     * Scope: Filter berdasarkan tingkat
     */
    public function scopeByLevel($query, $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }
}
