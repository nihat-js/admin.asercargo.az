<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Class Item
 * @package App
 */
class Item extends Model
{
    /**
     * @var string
     */
    protected $table = 'item';
    /**
     * @var string[]
     */
    protected $fillable = [
        'category_id',
        'code',
        'price',
        'price_usd',
        'currency_id', //for invoice price
        'quantity',
        'title',
        'custom_cat_id',
        'subCat',
        'invoice_doc',
        'invoice_confirmed',
        'invoice_status',
        'package_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at'
    ];

    /**
     * @return BelongsTo
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    /**
     * @return HasManyThrough
     */
    public function statuses(): HasManyThrough
    {
        return $this->hasManyThrough(
            PackageStatus::class,
            Package::class,
            'id',
            'package_id',
            'package_id'
        );
    }

    public function carrierLog()
    {   
        return $this->hasMany(PackageCarrierStatusTracking::class, 'internal_id', 'internal_id');
    }
}
