<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    /**
     * The attributes that are mass assignable untuk menyimpan riwayat password user,
     * dengan tujuan mencegah reuse 3 password terakhir
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'password',
    ];

    /**
     * The attributes that should be hidden untuk serialization
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Relationship dengan User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
