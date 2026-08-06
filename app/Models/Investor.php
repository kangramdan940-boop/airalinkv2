<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investor extends Model
{
    use SoftDeletes;

    protected $table = 'investors';

    protected $fillable = [
        'user_id',
        'persentase',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'persentase' => 'double',
        'status' => 'boolean',
    ];

    /* Relations */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* Scopes */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /* Helpers */
    public function hitungBagiHasil(float $totalKeuntungan): float
    {
        return $totalKeuntungan * ($this->persentase / 100);
    }
}
