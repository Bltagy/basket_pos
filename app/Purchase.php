<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable =[

        "reference_no", "user_id", "warehouse_id", "supplier_id", "item", "total_qty", "total_discount", "total_tax", "total_cost", "order_tax_rate", "order_tax", "order_discount", "shipping_cost", "grand_total","paid_amount", "status", "payment_status", "document", "note", "created_at"
    ];

    public function supplier()
    {
    	return $this->belongsTo('App\Supplier');
    }

    public function warehouse()
    {
    	return $this->belongsTo('App\Warehouse');
    }

    public function products()
    {
    	return $this->hasMany('App\ProductPurchase','purchase_id');
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
