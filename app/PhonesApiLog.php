<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhonesApiLog extends Model
{
    protected $table = 'phones_api_log';
    protected $fillable = [
        'phone',
        'client_id',
        'user_id'
    ];
}
