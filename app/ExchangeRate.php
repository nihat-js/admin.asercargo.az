<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $table = 'exchange_rate';
    protected $fillable = [
        'from_date',
        'to_date',
        'rate',
        'from_currency_id',
        'to_currency_id',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
