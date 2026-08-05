<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;

trait RaidModel
{
    public static function bootRaidModel(): void
    {
        static::saving(function ($model) {
            if (auth()->check() && in_array('updated_by', $model->getFillable()) || \Schema::hasColumn($model->getTable(), 'updated_by')) {
                $model->updated_by = auth()->id();
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && \Schema::hasColumn($model->getTable(), 'created_by')) {
                $model->updated_by = null;
                $model->updated_at = null;
                $model->created_by = auth()->id();
            }
        });

        static::saved(function ($model) {
            if (property_exists($model, 'log_table') && $model->log_table) {
                $log = $model->getAttributes();
                $log[$model->log_table_fk] = $log['id'];
                unset($log['id']);
                DB::table($model->log_table)->insert($log);
            }
        });

        static::deleting(function ($model) {
            if (property_exists($model, 'log_table') && $model->log_table) {
                $log = $model->getAttributes();
                $log[$model->log_table_fk] = $log['id'];
                unset($log['id']);
                DB::table($model->log_table)->insert($log);
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public static function saveData($request, $identifier = 'id')
    {
        $record = static::prepare($request, $identifier);
        $record->fill($request->all());
        $record->save();

        return $record;
    }

    public static function prepare($request, $identifier = 'id')
    {
        if ($request->has($identifier) && $request->get($identifier)) {
            return static::findOrFail($request->get($identifier));
        }

        return new static;
    }
}
