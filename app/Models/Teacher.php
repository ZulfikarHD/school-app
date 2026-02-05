<?php

namespace App\Models;

use App\Enums\StatusKepegawaian;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes untuk teacher data yang mencakup
     * biodata pribadi, data kepegawaian, dan kualifikasi akademik
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'nip',
        'nik',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_hp',
        'email',
        'foto',
        'status_kepegawaian',
        'tanggal_mulai_kerja',
        'tanggal_berakhir_kontrak',
        'kualifikasi_pendidikan',
        'is_active',
    ];

    /**
     * Cast attributes ke tipe data yang sesuai untuk handling date, boolean, dan enum
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'tanggal_mulai_kerja' => 'date',
            'tanggal_berakhir_kontrak' => 'date',
            'is_active' => 'boolean',
            'status_kepegawaian' => StatusKepegawaian::class,
        ];
    }

    /**
     * Relationship many-to-one dengan User untuk autentikasi
     * dimana satu teacher memiliki satu user account untuk login
     *
     * @return BelongsTo<User, Teacher>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship many-to-many dengan Subject melalui pivot table teacher_subjects
     * dimana satu teacher bisa mengajar multiple subjects
     *
     * @return BelongsToMany<Subject>
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects')
            ->withPivot('class_id', 'tahun_ajaran', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Relationship untuk mendapatkan mata pelajaran utama yang diajarkan guru
     *
     * @return BelongsToMany<Subject>
     */
    public function primarySubjects(): BelongsToMany
    {
        return $this->subjects()->wherePivot('is_primary', true);
    }

    /**
     * Relationship one-to-many dengan TeacherAttendance untuk tracking
     * presensi clock in/out guru
     *
     * @return HasMany<TeacherAttendance>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class);
    }

    /**
     * Relationship one-to-many dengan TeacherLeave untuk tracking
     * permohonan cuti guru
     *
     * @return HasMany<TeacherLeave>
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(TeacherLeave::class);
    }

    /**
     * Helper method untuk check apakah guru masih aktif
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Helper method untuk mendapatkan umur guru dalam tahun
     */
    public function getAge(): ?int
    {
        return $this->tanggal_lahir?->age;
    }

    /**
     * Helper method untuk mendapatkan masa kerja dalam tahun
     */
    public function getMasaKerja(): ?int
    {
        return $this->tanggal_mulai_kerja?->diffInYears(now());
    }

    /**
     * Accessor untuk mendapatkan URL foto profil dengan fallback ke default avatar
     */
    public function getFotoUrlAttribute(): string
    {
        if ($this->foto) {
            return asset('storage/'.$this->foto);
        }

        // Default avatar berdasarkan jenis kelamin
        $initial = strtoupper(substr($this->nama_lengkap, 0, 1));

        return "https://ui-avatars.com/api/?name={$initial}&background=10b981&color=fff&size=128";
    }

    /**
     * Accessor untuk format NIP dengan prefix yang lebih readable
     */
    public function getFormattedNipAttribute(): ?string
    {
        return $this->nip ? 'NIP: '.$this->nip : null;
    }

    /**
     * Accessor untuk mendapatkan nama mata pelajaran utama sebagai string
     */
    public function getPrimarySubjectNamesAttribute(): string
    {
        return $this->primarySubjects->pluck('nama_mapel')->implode(', ') ?: '-';
    }

    /**
     * Scope query untuk filter guru yang aktif
     *
     * @param  Builder<Teacher>  $query
     * @return Builder<Teacher>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query untuk filter guru yang tidak aktif
     *
     * @param  Builder<Teacher>  $query
     * @return Builder<Teacher>
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope query untuk filter berdasarkan status kepegawaian
     *
     * @param  Builder<Teacher>  $query
     * @return Builder<Teacher>
     */
    public function scopeByStatus(Builder $query, string|StatusKepegawaian $status): Builder
    {
        $statusValue = $status instanceof StatusKepegawaian ? $status->value : $status;

        return $query->where('status_kepegawaian', $statusValue);
    }

    /**
     * Scope query untuk search guru by nama, NIP, atau NIK
     *
     * @param  Builder<Teacher>  $query
     * @return Builder<Teacher>
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nama_lengkap', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%")
                ->orWhere('nik', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Scope query untuk filter guru yang kontraknya akan berakhir dalam N hari
     *
     * @param  Builder<Teacher>  $query
     * @return Builder<Teacher>
     */
    public function scopeContractEndingSoon(Builder $query, int $days = 30): Builder
    {
        return $query->where('status_kepegawaian', StatusKepegawaian::KONTRAK->value)
            ->whereNotNull('tanggal_berakhir_kontrak')
            ->whereBetween('tanggal_berakhir_kontrak', [now(), now()->addDays($days)]);
    }
}
