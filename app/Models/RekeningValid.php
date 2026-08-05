<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekeningValid extends Model
{
    protected $table = 'rekening_valids';

    protected $fillable = [
        'no_rekening',
        'atas_nama',
        'kode_bank',
        'nama_bank',
        'total_trx',
    ];
}
