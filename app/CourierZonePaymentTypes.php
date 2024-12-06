<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class CourierZonePaymentTypes extends Model
{
    protected $table = 'courier_zone_payment_type';
    protected $fillable = [
        'zone_id',
        'delivery_payment_type_id',
        'courier_payment_type_id',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];

    protected static function boot()
    {
        parent ::boot();
        static ::addGlobalScope(new DeletedScope('courier_zone_payment_type'));
    }
}
