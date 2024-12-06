<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BalanceLog extends Model
{
    protected $table = 'balance_log';
    protected $fillable = [
        'payment_code',
        'amount',
        'amount_azn',
        'client_id',
        'status',
        'type', //cash, cart, balance, back, manual
        'platform', // for only yigim payment system
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
