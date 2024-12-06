<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PrintReceiptLog extends Model
{
    protected $table = 'print_receipt_log';
    protected $fillable = [
        'text',
        'status',
        'created_by'
    ];
}
