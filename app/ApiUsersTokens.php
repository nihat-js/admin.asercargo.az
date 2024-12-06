<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiUsersTokens extends Model
{
    protected $table = 'api_users_tokens';
    protected $fillable = [
        'token',
        'last_active_time',
        'user_id'
    ];
}
