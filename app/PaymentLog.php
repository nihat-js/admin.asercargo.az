<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    protected $table = 'payment_log';
    protected $fillable = [
        'package_id',
        'client_id',
        'payment',
        'currency_id',
        'type', // 1 -cash, 2 - pos or cart, 3 - balance, 4 - by_admin
        'is_courier_order', // 1 - yes, 2- no
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
