<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Traits\Utilities;

class User extends Authenticatable
{
    use Notifiable, HasRoles, Utilities;

    protected $table = 'sys_users';

    public $remember_token = false;

    protected $fillable = [
        'username',
        'email',
        'password',
        'token',
        'last_login',
        'status',
    ];

    protected $hidden = [
        'password',
        'token',
    ];

    protected function casts(): array
    {
        return [
            'last_login' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* Relations */
    public function limit()
    {
        return $this->hasOne(Transaksi\Limit::class, 'user_id');
    }

    public function transfers()
    {
        return $this->hasMany(Transaksi\Transfer::class, 'user_id');
    }

    public function tunais()
    {
        return $this->hasMany(Transaksi\Tunai::class, 'user_id');
    }

    /* Scopes */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /* Helpers */
    public function isActive(): bool
    {
        return $this->status == 1;
    }
}
