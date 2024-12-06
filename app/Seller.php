<?php

namespace App;

use App\Scopes\DeletedScope;
use App\Scopes\SellersOnlyCollectorScope;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $table = 'seller';
    protected $fillable = [
        'has_site',
        'only_collector',
        'name',
        'title',
        'img',
        'url',
        'category_id',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new DeletedScope('seller'));

        //static::addGlobalScope(new SellersOnlyCollectorScope());
    }

    public function country()
    {
        return $this->belongsToMany('App\Countries', 'seller_location', 'seller_id', 'location_id');
    }

    public function category()
    {
        return $this->belongsToMany('App\StoreCategory', 'seller_category', 'seller_id', 'category_id');
    }

}
