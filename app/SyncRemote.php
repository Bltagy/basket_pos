<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SyncRemote extends Model
{

    protected $connection = 'remote';

    protected $table = 'syncs';

    protected $dates = ['deleted_at'];
    protected $guarded =[];

//    protected static function boot() {
//        parent::boot();
//
//        static::creating(function ($model) {
//            $model->created_by = is_object(Auth::guard(config('app.guards.web'))->user()) ? Auth::guard(config('app.guards.web'))->user()->id : 1;
//            $model->origin = env('SYNC_SERVER');
//        });
//    }
}
