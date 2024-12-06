<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class CourierZones extends Model
{
    protected $table = 'courier_zones';
    protected $fillable = [
        'name_en',
        'name_az',
        'name_ru',
        'default_tariff', // decimal 18,2
        'created_by',
        'deleted_by',
        'deleted_at'
    ];

    protected static function boot()
    {
        parent ::boot();
        static ::addGlobalScope(new DeletedScope('courier_zones'));
    }
}
