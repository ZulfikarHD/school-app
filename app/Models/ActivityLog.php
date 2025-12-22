<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    /**
     * The attributes that are mass assignable untuk logging aktivitas user,
     * mencakup login, logout, CRUD operations, dan akses unauthorized
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'status',
    ];

    /**
     * Get the attributes that should be cast dengan JSON untuk old/new values
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    /**
     * Relationship dengan User untuk mengetahui siapa yang melakukan action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
