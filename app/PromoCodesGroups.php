<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class PromoCodesGroups extends Model
{
    protected $table = 'promo_codes_groups';
    protected $fillable = [
        'name', //100
        'percent', //2
        'count', //7
        //'used_count', //7
        'created_by',
        //'deleted_by',
        //'deleted_at'
    ];

    protected static function boot()
    {
        parent ::boot();
        static ::addGlobalScope(new DeletedScope('promo_codes_groups'));
    }
}
