<?php

namespace App\Models\Transaksi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\RaidModel;
use App\Models\Traits\Utilities;
use App\Models\User;

class UangCash extends Model
{
    use SoftDeletes, RaidModel, Utilities;

    protected $table = 'trans_uang_cash';

    protected $fillable = [
        'user_id',
        'nominal',
        'cabang_toko',
        'keterangan',
        'whatsapp',
        'total',
    ];

    protected $casts = [
        'nominal' => 'double',
        'total' => 'integer',
    ];

    // Log table
    public $log_table = 'log_trans_uang_cash';
    public $log_table_fk = 'trans_id';

    /* Relations */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
