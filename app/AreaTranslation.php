<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AreaTranslation extends Model
{
    protected $table = 'area_translations';

    public $timestamps = false;
    protected $fillable = ['name'];
}
