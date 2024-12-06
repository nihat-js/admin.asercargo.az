<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Countries extends Model
{
    protected $table = 'countries';
    protected $fillable = [
        'name',
        'code',
        'flag',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];

   public function seller()
   {
      return $this->belongsToMany('App\Seller', 'seller_location', 'id', 'location_id');
   }
}
