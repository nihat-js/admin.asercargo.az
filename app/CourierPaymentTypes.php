<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class CourierPaymentTypes extends Model
{
    protected $table = 'courier_payment_types';
    protected $fillable = [
        'name_en',
        'name_az',
        'name_ru',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];

    protected static function boot()
    {
        parent ::boot();
        static ::addGlobalScope(new DeletedScope('courier_payment_types'));
    }
}
