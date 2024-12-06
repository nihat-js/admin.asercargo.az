<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'position';
    protected $fillable = [
        'active_tracking_log',
        'name',
        'location_id',
        'created_by',
        'deleted_by',
        'deleted_at',
        'partner_position_id'
    ];
}
