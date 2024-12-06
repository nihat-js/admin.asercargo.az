<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AWB extends Model
{
    protected $table = 'AWB';
    protected $fillable = [
        'number',
        'series',
        'location_id',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
