<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Batches extends Model
{
    protected $table = 'batches';
    protected $fillable = [
        'name',
        'location_id',
        'count',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
