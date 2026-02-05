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

    /**
     * Relationship dengan Guardian untuk parent portal
     * dimana user dengan role PARENT akan ter-link ke guardian record
     */
    public function guardian()
    {
        return $this->hasOne(Guardian::class);
    }

    /**
     * Relationship one-to-one dengan Teacher untuk profil guru
     * dimana user dengan role TEACHER akan ter-link ke teacher record
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<Teacher>
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Helper method untuk mendapatkan students yang terhubung dengan parent user
     * melalui guardian relationship
     */
    public function children()
    {
        return $this->hasManyThrough(
            Student::class,
            Guardian::class,
            'user_id', // Foreign key on guardians table
            'id', // Foreign key on students table (through pivot)
            'id', // Local key on users table
            'id' // Local key on guardians table
        );
    }

    /**
     * Relationship one-to-many dengan TeacherAttendance untuk tracking
     * presensi clock in/out guru
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TeacherAttendance>
     */
    public function teacherAttendances()
    {
        return $this->hasMany(TeacherAttendance::class, 'teacher_id');
    }

    /**
     * Relationship one-to-many dengan TeacherLeave untuk tracking
     * permohonan cuti guru
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TeacherLeave>
     */
    public function teacherLeaves()
    {
        return $this->hasMany(TeacherLeave::class, 'teacher_id');
    }

    /**
     * Relationship many-to-many dengan Subject untuk tracking
     * mata pelajaran yang diajar oleh guru
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Subject>
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects', 'teacher_id', 'subject_id')
            ->withPivot('class_id', 'tahun_ajaran')
            ->withTimestamps();
    }

    /**
     * Scope query untuk filter user dengan role TEACHER
     * yang digunakan untuk listing guru
     *
     * @param  \Illuminate\Database\Eloquent\Builder<User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    public function scopeTeachers($query)
    {
        return $query->where('role', 'TEACHER');
    }

    /**
     * Scope query untuk filter user dengan status ACTIVE
     * Note: User model menggunakan column 'status' (enum: ACTIVE, INACTIVE)
     * berbeda dengan model lain yang menggunakan 'is_active' (boolean)
     *
     * @param  \Illuminate\Database\Eloquent\Builder<User>  $query
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }
}
