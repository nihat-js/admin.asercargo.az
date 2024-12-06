<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class ProhibitedItem extends Model
{
   protected $table = 'prohibited_items';
   protected $fillable = [ 'item_az', 'item_ru','item_en','country_id' ];
   protected $casts = [
      'country_id' => 'integer',
   ];

   protected static function boot()
   {
      parent ::boot();
      static ::addGlobalScope(new DeletedScope('prohibited_items'));
   }

   public function country()
   {
      return $this -> belongsTo('App\Countries');
   }

}
