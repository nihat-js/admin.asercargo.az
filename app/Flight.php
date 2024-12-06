<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    protected $table = 'flight';
    protected $fillable = [
        'name',
        'carrier',
        'flight_number',
        'awb',
        'departure',
        'destination',
//        'fact_take_off',
//        'fact_arrival',
//        'plan_take_off',
//        'plan_arrival',
        'location_id',
        'public',
        'closed_by',
        'closed_at',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
