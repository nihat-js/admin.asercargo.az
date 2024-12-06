<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestLog extends Model
{
    protected $table = 'testlog';
    protected $fillable = [
        'tracks',
        'ids'
    ];
}
