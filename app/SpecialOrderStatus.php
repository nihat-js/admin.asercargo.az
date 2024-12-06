<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpecialOrderStatus extends Model
{
    protected $table = 'special_order_status';
    protected $fillable = [
        'order_id',
        'status_id',
        'created_by'
    ];
}
