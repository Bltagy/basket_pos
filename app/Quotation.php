<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable =[

        "reference_no", "user_id", "biller_id", "supplier_id", "customer_id", "warehouse_id", "item", "total_qty", "total_discount", "total_tax", "total_price", "order_tax_rate", "order_tax", "order_discount", "shipping_cost", "grand_total", "quotation_status","document", "note"
    ];

    public function biller()
    {
    	return $this->belongsTo('App\Biller');
    }

    public function customer()
    {
    	return $this->belongsTo('App\Customer');
    }

    public function supplier()
    {
    	return $this->belongsTo('App\Supplier');
    }

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
