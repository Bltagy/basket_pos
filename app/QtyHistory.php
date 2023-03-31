<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class QtyHistory extends Model
{
	protected $table = 'qry_histories';
    protected $fillable =[

        "product_id", "old_qty", "new_qty", "created_by"
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
