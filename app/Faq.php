<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'faqs';
    protected $fillable=['id','answer_az','answer_ru','answer_en','question_az','question_ru','question_en'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new DeletedScope('faqs'));
    }
}
