<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrackingLog extends Model
{
    protected $table = 'tracking_log';
    protected $fillable = [
        'package_id',
        'operator_id',
        'container_id',
        'position_id',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
