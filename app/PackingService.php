<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PackingService extends Model
{
    protected $table = 'packing_services';
    protected $fillable = [
        'title',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
