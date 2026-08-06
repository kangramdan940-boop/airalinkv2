<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengeluaran extends Model
{
    use SoftDeletes;

    protected $table = 'pengeluaran';

    protected $fillable = [
        'user_id',
        'nama',
        'nominal',
        'tipe',
        'bulan',
        'keterangan',
    ];

    protected $casts = [
        'nominal' => 'double',
    ];

    // Preset items
    const PRESETS = [
        ['nama' => 'Gaji', 'nominal' => 2000000, 'tipe' => 'bulanan'],
        ['nama' => 'Makan', 'nominal' => 15000, 'tipe' => 'harian'],
        ['nama' => 'Listrik', 'nominal' => 200000, 'tipe' => 'bulanan'],
    ];

    /* Relations */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Hitung nominal per bulan
     * - bulanan: langsung nominal
     * - harian: nominal x 30
     */
    public function getNominalBulananAttribute(): float
    {
        return $this->tipe === 'harian' ? $this->nominal * 30 : $this->nominal;
    }
}
