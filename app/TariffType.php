<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TariffType extends Model
{
    // contract detail type
    protected $table = 'tariff_types';
    protected $fillable = [
        'name',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
