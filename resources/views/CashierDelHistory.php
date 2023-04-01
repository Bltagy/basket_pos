<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CashierDelHistory extends Model
{
	protected $table = 'cashier_del_histories';
    protected $fillable =[

        "code", "admin_id", "cashier_id", "product_id"
    ];
    protected static function boot() {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = is_object(Auth::guard(config('app.guards.web'))->user()) ? Auth::guard(config('app.guards.web'))->user()->id : 1;
        });
    }
    public function user()
    {
        return $this->belongsTo('App\User', 'created_by');
    }
}
