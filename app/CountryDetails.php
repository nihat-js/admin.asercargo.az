<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class CountryDetails extends Model
{
    protected $table = 'country_details';
    protected $fillable = ['id','country_id','title','information'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new DeletedScope('country_details'));
    }

   public function country()
   {
      return $this -> belongsTo('App\Countries','country_id');
   }
}
