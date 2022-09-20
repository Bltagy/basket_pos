<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReturnPurchase extends Model
{
    protected $table = 'return_purchases';
    protected $fillable =[
        "reference_no", "user_id", "supplier_id", "warehouse_id", "account_id", "item", "total_qty", "total_discount", "total_tax", "total_cost","order_tax_rate", "order_tax", "grand_total", "document", "return_note", "staff_note", "created_at"
    ];

    public function supplier()
    {
    	return $this->belongsTo('App\Supplier');
    }

    public function warehouse()
    {
    	return $this->belongsTo('App\Warehouse');
    }

    public function user()
    {
    	return $this->belongsTo('App\User');
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
