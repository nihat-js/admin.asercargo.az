<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $table = 'contract';
    protected $fillable = [
        'system',
        'description',
        'start_date',
        'end_date',
        'default_option',
        'is_active',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
