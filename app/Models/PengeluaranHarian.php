<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengeluaranHarian extends Model
{
    use SoftDeletes;

    protected $table = 'pengeluaran_harian';

    protected $fillable = ['tanggal', 'nama', 'nominal', 'keterangan'];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'double',
    ];
}
