<?php

namespace App\Models\Transaksi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\RaidModel;
use App\Models\Traits\Utilities;
use App\Models\User;

class Transfer extends Model
{
    use SoftDeletes, RaidModel, Utilities;

    protected $table = 'trans_transfer_banks';

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
        'whatsapp',
    ];

    protected $casts = [
        'nominal' => 'double',
        'admin' => 'double',
    ];

    // Log table
    public $log_table = 'log_trans_transfer_banks';
    public $log_table_fk = 'trans_id';

    // Status constants
    const STATUS_DRAFT = 0;
    const STATUS_REQUEST = 1;
    const STATUS_PROCESS = 2;
    const STATUS_SUCCESS = 3;
    const STATUS_CANCEL = 4;

    /* Relations */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* Helpers */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_REQUEST => '<span class="badge bg-info">Request</span>',
            self::STATUS_PROCESS => '<span class="badge bg-warning">Process</span>',
            self::STATUS_SUCCESS => '<span class="badge bg-success">Success</span>',
            self::STATUS_CANCEL => '<span class="badge bg-secondary">Cancel</span>',
            default => '<span class="badge bg-light text-dark">-</span>',
        };
    }
}
