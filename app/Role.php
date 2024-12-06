<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = [
        'role',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
