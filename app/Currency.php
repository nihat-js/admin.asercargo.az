<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currency';
    protected $fillable = [
        'name',
        'code',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
