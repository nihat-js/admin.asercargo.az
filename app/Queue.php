<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $table = 'queue';
    protected $fillable = [
        'date',
        'type', //c - cashier (101-399); d - delivery (401-699); i - information (701-999))
        'no',
        'user_id',
        'location_id',
        'used', // 0 or 1
        'deleted_by',
        'deleted_at',
    ];
}
