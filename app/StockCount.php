<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockCount extends Model
{
    protected $table = 'stock_counts';
    protected $fillable =[
        "reference_no", "warehouse_id", "brand_id", "category_id", "user_id", "type", "initial_file", "final_file", "note", "is_adjusted"
    ];

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
