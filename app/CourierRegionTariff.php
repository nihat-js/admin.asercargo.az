<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class CourierRegionTariff extends Model
{
    protected $table = 'courier_region_tariffs';
    protected $fillable = [
        'name',
        'from_weight', // decimal 18,2
        'to_weight', // decimal 18,2
        'static_price', // decimal 18,2
        'dynamic_price', // decimal 18,2
        'created_by',
        'deleted_by',
        'deleted_at'
    ];

    protected static function boot()
    {
        parent ::boot();
        static ::addGlobalScope(new DeletedScope('courier_region_tariffs'));
    }
}
