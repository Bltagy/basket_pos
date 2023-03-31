<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;

    protected $fillable = [
        'name', 'email', 'password',"phone","company_name", "role_id", "biller_id", "warehouse_id", "is_active", "is_deleted", "supervisor_code"
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function isActive()
    {
        return $this->is_active;
    }

    public function holiday() {
        return $this->hasMany('App\Holiday');
    }

    public function sales() {
        return $this->hasMany('App\Sale','delivery_id');
    }

    public function shiftSales() {
        return $this->hasMany('App\Sale','user_id');
    }

    public function cashierLogs() {
        return $this->hasMany('App\CashierLog');
    }

    public function salesCount() {
        return $this->hasMany('App\Sale','delivery_id')->where('sale_status',3);

    }

    public function salesDueAmount() {
        return $this->hasMany('App\Sale','delivery_id')->where('payment_status',2);

    }

    public function getSaleCountAttribute()
    {
        return $this->whereHas('sales', function ($q) {
            $q->where('sale_status', 2);
            $q->where('delivery_id', $this->id);
        })->count();
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
    }
}
