<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomCategory extends Model
{
    protected $table = 'custom_category';
    protected $fillable = [
        'id',
        'parentId',
        'goodsNameAz',
        'goodsNameEn',
        'goodsNameRu',
        'isDeleted',
        'created_by',
        'updated_at'
    ];

}
