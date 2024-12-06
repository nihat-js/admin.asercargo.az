<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';
    protected $fillable = [
        'city',
        'country_id',
        'name',
//        'is_volume',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
