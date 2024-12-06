<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class StoreCategory extends Model
{
    protected $table = 'store_category';
    protected $fillable = ['name_az','name_ru','name_en','created_by'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new DeletedScope('store_category'));
    }
   public function seller()
   {
      return $this->belongsToMany('App\Seller', 'seller_location', 'category_id', 'seller_id');
   }
}
