<?php

namespace App\Models\Transaksi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\RaidModel;
use App\Models\Traits\Utilities;

class Tabungan extends Model
{
    use SoftDeletes, RaidModel, Utilities;

    protected $table = 'trans_tabungan';

    protected $fillable = [
        'nama',
        'kode_tabungan',
        'status_transaksi',
        'nominal',
        'saldo',
        'berita',
    ];

    protected $casts = [
        'nominal' => 'double',
        'saldo' => 'double',
    ];

    // Log table
    public $log_table = 'log_trans_tabungan';
    public $log_table_fk = 'trans_id';
}
