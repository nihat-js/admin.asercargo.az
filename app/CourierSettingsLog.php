<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourierSettingsLog extends Model
{
    protected $table = 'courier_settings_log';
    protected $fillable = [
        'daily_limit',
        'closing_time',
        'amount_for_urgent',
        'created_by'
    ];
}
