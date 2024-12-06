<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{

    public const delivered = 3;
    public const order_placed = 13;
    public const courier_canclled = 33;
    public const delivered_by_courier = 34;
    public const delivered_by_azeripost = 44;
    public const customs_clearance_started = 47;
    public const customs_control_started = 48;
    public const customs_clearance_in_progress = 49;
    public const released_from_customs = 50;



    protected $table = 'lb_status';
    protected $fillable=['id','status_az','status_ru','status_en'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new DeletedScope('lb_status'));
    }
}
