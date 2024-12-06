<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $table = 'api_log';
    protected $fillable = [
        'request',
        'response'
    ];
}
