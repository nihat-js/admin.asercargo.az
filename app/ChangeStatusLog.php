<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChangeStatusLog extends Model
{
    protected $table = 'change_status_log';

    protected $fillable = [
        'package_id',
        'old_status_id',
        'new_status_id',
        'remark',
        'created_by'
    ];
}
