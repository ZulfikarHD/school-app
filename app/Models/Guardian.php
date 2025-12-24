<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guardian extends Model
{
    /** @use HasFactory<\Database\Factories\GuardianFactory> */
    use HasFactory;

    /**
     * Mass assignable attributes untuk data orang tua/wali yang mencakup
     * identitas, pekerjaan, pendidikan, dan kontak
     *
     * @var list<string>
     */
    protected $fillable = [
        'nik',
        'nama_lengkap',
        'hubungan',
        'pekerjaan',
        'pendidikan',
        'penghasilan',
        'no_hp',
        'email',
        'alamat',
        'user_id',
    ];

    /**
     * Relationship many-to-many dengan Student melalui pivot table student_guardian
     * dimana satu guardian bisa punya multiple children di sekolah
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_guardian')
            ->withPivot('is_primary_contact')
            ->withTimestamps();
    }

    /**
     * Relationship dengan User untuk akun portal orang tua
     * dimana guardian yang punya akun bisa login dan view data anak
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper method untuk check apakah guardian sudah punya akun portal
     */
    public function hasPortalAccount(): bool
    {
        return $this->user_id !== null;
    }

    /**
     * Helper method untuk mendapatkan label hubungan yang lebih readable
     */
    public function getHubunganLabelAttribute(): string
    {
        return match ($this->hubungan) {
            'ayah' => 'Ayah Kandung',
            'ibu' => 'Ibu Kandung',
            'wali' => 'Wali',
            default => $this->hubungan,
        };
    }

    /**
     * Scope query untuk filter guardian berdasarkan hubungan
     */
    public function scopeByRelation($query, $hubungan)
    {
        return $query->where('hubungan', $hubungan);
    }

    /**
     * Scope query untuk guardian yang sudah punya akun portal
     */
    public function scopeWithPortalAccount($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope query untuk guardian yang belum punya akun portal
     */
    public function scopeWithoutPortalAccount($query)
    {
        return $query->whereNull('user_id');
    }
}
