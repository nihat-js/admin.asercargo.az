<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class CourierOrders extends Model
{
    protected $table = 'courier_orders';

    protected $fillable = [
        'packages',
        'client_id',
        'date',
        'area_id',
        'region_id',
        'metro_station_id',
        'address',
        'phone',
        //'remark',
        'amount', // 18,2
        'delivery_amount', // 18,2
        'total_amount', // 18,2 -> amount (courier) + delivery
        'courier_payment_type_id',
        'delivery_payment_type_id',
        'urgent', // 1,0
        'created_by',
        'post_zip',
        'courier_id',
        'azerpost_track',
        'order_weight',
        'is_send_azerpost',
        'is_set_azerpost',
        'is_error'
    ];

    protected static function boot()
    {
        parent ::boot();
        static ::addGlobalScope(new DeletedScope('courier_orders'));
    }
}
