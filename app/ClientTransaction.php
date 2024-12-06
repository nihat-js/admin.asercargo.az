<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientTransaction extends Model
{
    protected $table = 'client_transaction';
    protected $fillable = [
        'amount',
        'date',
        'description',
        'purchase_description',
        'source',
        'type',
        'currency_id',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
