<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PackageFiles extends Model
{
    protected $table = 'package_files';
    protected $fillable = [
        'domain',
        'url',
        'package_id',
        'type', // 1 - image, 2 - file
        'name',
        'extension',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
