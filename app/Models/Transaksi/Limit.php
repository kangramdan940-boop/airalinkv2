<?php

namespace App\Models\Transaksi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\RaidModel;
use App\Models\Traits\Utilities;
use App\Models\User;

class Limit extends Model
{
    use SoftDeletes, RaidModel, Utilities;

    protected $table = 'trans_limit_harians';

    protected $fillable = [
        'user_id',
        'limit',
    ];

    // Log table
    public $log_table = 'log_trans_limit_harians';
    public $log_table_fk = 'trans_id';

    /* Relations */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
