<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductReturn extends Model
{
    protected $table = 'product_returns';
    protected $fillable =[
        "return_id", "product_id", "variant_id", "imei_number", "product_batch_id", "qty", "sale_unit_id", "net_unit_price", "discount", "tax_rate", "tax", "total"
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
