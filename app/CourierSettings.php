<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourierSettings extends Model
{
    protected $table = 'courier_settings';
    // only update
//    protected $fillable = [
//        'daily_limit',
//        'closing_time',
//        'amount_for_urgent'
//    ];
}
