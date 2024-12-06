<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChangeAccountLog extends Model
{
    protected $table = 'change_account_log';
    protected $fillable = [
        'old_client_id',
        'new_client_id',
        'package_id',
        'remark',
        'created_by',
        'which_platform'
    ];
}
