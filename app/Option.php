<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $table = 'options';
    protected $fillable = [
        'title',
        'device1',
        'device2',
        'location_id',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
