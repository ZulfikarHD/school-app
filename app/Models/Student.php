<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes untuk student data yang mencakup
     * biodata pribadi, alamat lengkap, dan data akademik
     *
     * @var list<string>
     */
    protected $fillable = [
        'nis',
        'nisn',
        'nik',
        'nama_lengkap',
        'nama_panggilan',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'anak_ke',
        'jumlah_saudara',
        'status_keluarga',
        'alamat',
        'rt_rw',
        'kelurahan',
        'kecamatan',
        'kota',
        'provinsi',
        'kode_pos',
        'no_hp',
        'email',
        'foto',
        'kelas_id',
        'tahun_ajaran_masuk',
        'tanggal_masuk',
        'status',
    ];

    /**
     * Cast attributes ke tipe data yang sesuai untuk handling date dan boolean
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_masuk' => 'date',
            'anak_ke' => 'integer',
            'jumlah_saudara' => 'integer',
        ];
    }

    /**
     * Relationship many-to-one dengan SchoolClass
     */
    public function kelas(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'kelas_id');
    }

    /**
     * Relationship many-to-many dengan Guardian melalui pivot table student_guardian
     * dimana satu siswa bisa punya multiple guardians (ayah, ibu, wali)
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian')
            ->withPivot('is_primary_contact')
            ->withTimestamps();
    }

    /**
     * Relationship untuk mendapatkan kontak utama (primary contact) dari guardians
     * yang digunakan untuk notifikasi dan komunikasi dengan orang tua
     */
    public function primaryGuardian(): BelongsToMany
    {
        return $this->guardians()->wherePivot('is_primary_contact', true);
    }

    /**
     * Relationship one-to-many dengan StudentClassHistory untuk tracking
     * riwayat perpindahan kelas siswa dari tahun ke tahun
     */
    public function classHistory(): HasMany
    {
        return $this->hasMany(StudentClassHistory::class);
    }

    /**
     * Relationship one-to-many dengan StudentStatusHistory untuk tracking
     * perubahan status siswa (aktif, mutasi, DO, lulus)
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(StudentStatusHistory::class);
    }

    /**
     * Helper method untuk check apakah siswa masih aktif
     */
    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    /**
     * Helper method untuk mendapatkan umur siswa dalam tahun
     */
    public function getAge(): int
    {
        return $this->tanggal_lahir->age;
    }

    /**
     * Accessor untuk format NIS dengan prefix yang lebih readable
     */
    public function getFormattedNisAttribute(): string
    {
        return 'NIS-'.$this->nis;
    }

    /**
     * Scope query untuk filter siswa berdasarkan status
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope query untuk filter siswa berdasarkan kelas
     */
    public function scopeByClass($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    /**
     * Scope query untuk filter siswa berdasarkan tahun ajaran
     */
    public function scopeByAcademicYear($query, $tahunAjaran)
    {
        return $query->where('tahun_ajaran_masuk', $tahunAjaran);
    }

    /**
     * Scope query untuk search siswa by nama, NIS, atau NISN
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nama_lengkap', 'like', "%{$search}%")
                ->orWhere('nis', 'like', "%{$search}%")
                ->orWhere('nisn', 'like', "%{$search}%");
        });
    }
}
