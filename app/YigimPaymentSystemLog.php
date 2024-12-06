<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class YigimPaymentSystemLog extends Model
{
    protected $table = 'yigim_ps_log';
    protected $fillable = [
        'client_id',
        'receipt_no',
        'amount', // azn
        'amount_usd', //usd
        'platform', //emanat, million etc.
        'time',
        'checked', // checked by yigim
        'status' // 0 - unsuccess, 1 - success
    ];
}
