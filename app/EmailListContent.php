<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailListContent extends Model
{
    protected $table = 'email_list_content';

    public $timestamps = false;
    protected $fillable=[
        'id',
        'title_az',
        'title_ru',
        'title_en',
        'subject_az',
        'subject_ru',
        'subject_en',
        'content_az',
        'content_ru',
        'content_en',
        'list_inside_az',
        'list_inside_ru',
        'list_inside_en',
        'content_bottom_az',
        'content_bottom_ru',
        'content_bottom_en',
        'button_name_az',
        'button_name_ru',
        'button_name_en',
        'sms_az',
        'sms_ru',
        'sms_en',
        'push_content_az',
        'push_content_ru',
        'push_content_en'
    ];
}
