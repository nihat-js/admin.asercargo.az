<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class PromoCodes extends Model
{
    protected $table = 'promo_codes';
    protected $fillable = [
        'code', //15
        'group_id',
        'percent', //2
        //'client_id',
        //'used_at',
        //'real_price', //18,2
        //'discount', //18,2
        //'discounted_price', //18,2
        'created_by',
        //'deleted_by',
        //'deleted_at'
    ];

    protected static function boot()
    {
        parent ::boot();
        static ::addGlobalScope(new DeletedScope('promo_codes'));
    }
}
