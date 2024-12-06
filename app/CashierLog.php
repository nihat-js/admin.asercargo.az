<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashierLog extends Model
{
    protected $table = 'cashier_log';
    protected $fillable = [
        'payment_azn',
        'payment_usd',
        'added_to_balance', //azn
        'old_balance', //usd
        'new_balance', //usd
        'client_id',
        'receipt',
        'type',
        'created_by'
    ];
}
