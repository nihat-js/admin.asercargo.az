<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class CourierDailyLimits extends Model
{
    protected $table = 'courier_daily_limits';
    protected $fillable = [
        'date',
        'count',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];

    protected static function boot()
    {
        parent ::boot();
        static ::addGlobalScope(new DeletedScope('courier_daily_limits'));
    }
}
