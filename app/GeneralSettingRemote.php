<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GeneralSettingRemote extends Model
{
    protected $connection = 'remote';

    protected $table = 'general_settings';

    protected $guarded =[];
}
