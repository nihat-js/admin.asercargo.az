<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class CourierAreas extends Model
{
    protected $table = 'courier_areas';
    protected $fillable = [
        'zone_id',
        'name_en',
        'name_az',
        'name_ru',
        'tariff', // decimal 18,2
        'created_by',
        'deleted_by',
        'deleted_at'
    ];

    protected static function boot()
    {
        parent ::boot();
        static ::addGlobalScope(new DeletedScope('courier_areas'));
    }
}
