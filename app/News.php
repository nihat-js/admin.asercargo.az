<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    
    protected $fillable = ['name_az', 'content_az', 'name_ru', 'content_ru', 'name_en', 'content_en', 'slug', 'image', 'is_active', 'created_by', 'updated_by'];
}
