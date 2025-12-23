<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'status',
        'is_first_login',
        'last_login_at',
        'last_login_ip',
        'phone_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_first_login' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Relationship dengan ActivityLog untuk tracking aktivitas user
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Relationship dengan PasswordHistory untuk riwayat password
     */
    public function passwordHistories()
    {
        return $this->hasMany(PasswordHistory::class);
    }

    /**
     * Helper method untuk check apakah user memiliki role tertentu
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Helper method untuk check apakah user aktif
     */
    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }
}
