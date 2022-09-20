<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashierLog extends Model
{
    protected $fillable = ["user_id", "shift_id", "amount_got", "amount_deliver", "sales_amount", "approved_by", "warehouse_id", "date", "time_closed"];
    
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
