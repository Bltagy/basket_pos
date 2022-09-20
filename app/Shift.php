<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = ["opened_by", "closed_by", "time_closed", "total_amount", "date"];
    
    public function user()
    {
    	return $this->belongsTo('App\User');
    }

    public function warehouse()
    {
    	return $this->belongsTo('App\Warehouse');
    }

    protected static function booted()
    {
        static::created(function ($data) {
            $data = [
                'model'=> static::class,
                'model_id'=> $data->id,
                'action'=> 'create',
                'data'=> json_encode($data),
            ];
            Sync::create($data);
        });
        static::updated(function ($data) {
            $data = [
                'model'=> static::class,
                'model_id'=> $data->id,
                'action'=> 'update',
                'data'=> json_encode($data),
            ];
            Sync::create($data);
});
static::deleted(function ($data) {
    $data = [
        'model'=> static::class,
        'model_id'=> $data->id,
        'action'=> 'delete',
        'data'=> json_encode($data),
    ];
    Sync::create($data);
});
    }
}
