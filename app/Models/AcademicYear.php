<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * AcademicYear Model - Representasi tahun ajaran
 *
 * Model ini bertujuan untuk menyimpan data tahun ajaran yang digunakan
 * sebagai referensi untuk PSB dan aktivitas akademik lainnya
 *
 * @property int $id
 * @property string $name Format: 2025/2026
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AcademicYear extends Model
{
    /** @use HasFactory<\Database\Factories\AcademicYearFactory> */
    use HasFactory;

    /**
     * Daftar field yang dapat diisi secara mass assignment
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * Definisi cast untuk kolom-kolom yang memerlukan transformasi tipe data
     * dengan start_date dan end_date di-cast ke date untuk manipulasi Carbon
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi one-to-one ke PsbSetting dimana setiap tahun ajaran
     * memiliki satu konfigurasi PSB
     */
    public function psbSetting(): HasOne
    {
        return $this->hasOne(PsbSetting::class);
    }

    /**
     * Relasi one-to-many ke PsbRegistration dimana setiap tahun ajaran
     * dapat memiliki banyak pendaftaran PSB
     */
    public function psbRegistrations(): HasMany
    {
        return $this->hasMany(PsbRegistration::class);
    }

    /**
     * Scope untuk mendapatkan tahun ajaran yang aktif
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AcademicYear>  $query
     * @return \Illuminate\Database\Eloquent\Builder<AcademicYear>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk mendapatkan tahun ajaran yang sedang berjalan
     * berdasarkan tanggal saat ini
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AcademicYear>  $query
     * @return \Illuminate\Database\Eloquent\Builder<AcademicYear>
     */
    public function scopeCurrent($query)
    {
        $now = now();

        return $query->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }
}
