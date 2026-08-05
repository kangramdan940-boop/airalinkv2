<?php

namespace App\Models\Transaksi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\RaidModel;
use App\Models\Traits\Utilities;
use App\Models\User;

class Tunai extends Model
{
    use SoftDeletes, RaidModel, Utilities;

    protected $table = 'trans_tarik_tunais';

    protected $fillable = [
        'user_id',
        'kode_bank',
        'no_rekening',
        'atas_nama',
        'nominal',
        'cabang_toko',
        'status',
        'admin',
        'file',
        'keterangan',
    ];

    protected $casts = [
        'nominal' => 'double',
        'admin' => 'double',
    ];

    // Log table
    public $log_table = 'log_trans_tarik_tunais';
    public $log_table_fk = 'trans_id';

    /* Relations */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* Helpers */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            1 => '<span class="badge bg-info">Bukti Tf</span>',
            default => '<span class="badge bg-warning">-</span>',
        };
    }
}
