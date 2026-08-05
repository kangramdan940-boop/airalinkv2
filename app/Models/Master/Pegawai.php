<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\RaidModel;
use App\Models\Traits\Utilities;

class Pegawai extends Model
{
    use SoftDeletes, RaidModel, Utilities;

    protected $table = 'ref_pegawais';

    protected $fillable = [
        'nama',
        'telepon',
        'status',
        'keterangan',
    ];

    // Log table
    public $log_table = 'log_ref_pegawais';
    public $log_table_fk = 'ref_id';

    /* Scopes */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
