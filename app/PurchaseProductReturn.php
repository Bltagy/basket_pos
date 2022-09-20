<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseProductReturn extends Model
{
    protected $table = 'purchase_product_return';
    protected $fillable =[
        "return_id", "product_id", "product_batch_id", "variant_id", "imei_number", "qty", "purchase_unit_id", "net_unit_cost", "discount", "tax_rate", "tax", "total"
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
