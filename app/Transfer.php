<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable =[

        "reference_no", "user_id", "status", "from_warehouse_id", "to_warehouse_id", "item", "total_qty", "total_tax", "total_cost", "shipping_cost", "grand_total", "document", "note"
    ];

    public function fromWarehouse()
    {
    	return $this->belongsTo('App\Warehouse', 'from_warehouse_id');
    }

    public function toWarehouse()
    {
    	return $this->belongsTo('App\Warehouse', 'to_warehouse_id');
    }

    public function user()
    {
    	return $this->belongsTo('App\User', 'user_id');
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
