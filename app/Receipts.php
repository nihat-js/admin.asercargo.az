<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class Receipts extends Model
{
    protected $table = 'receipts';
    protected $fillable = [
        'receipt',
        'courier_order_id',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];

    protected static function boot()
    {
        parent ::boot();
        static ::addGlobalScope(new DeletedScope('receipts'));
    }
}
