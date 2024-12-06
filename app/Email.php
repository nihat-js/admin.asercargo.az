<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $table = 'email';
    protected $fillable = [
        'content',
        'fromEmail',
        'receiveDate',
        'seller',
        'subject',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
