<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedLoginAttempt extends Model
{
    /**
     * The attributes that are mass assignable untuk tracking failed login attempts
     * dengan tujuan implementasi brute force protection
     *
     * @var list<string>
     */
    protected $fillable = [
        'identifier',
        'ip_address',
        'attempts',
        'locked_until',
    ];

    /**
     * Get the attributes that should be cast
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'locked_until' => 'datetime',
        ];
    }

    /**
     * Helper method untuk check apakah account masih dalam status locked
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }
}
