<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'category';
    protected $fillable = [
        'name_en',
        'name_az',
        'name_ru',
        'created_by',
        'deleted_by',
        'deleted_at',
        'country_id'
    ];

    protected static function boot()
    {
        parent ::boot();
        static ::addGlobalScope(new DeletedScope('category'));
    }
}
