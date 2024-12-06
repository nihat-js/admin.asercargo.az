<?php

namespace App;

use App\Scopes\DeletedScope;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $table = 'package';
    protected $fillable = [
        'chargeable_weight',
        'volume_weight',
        'client_id',
        'customer_type_id',
        'client_name_surname', //for unknown package
        'console_name',
        'console',
        'gross_weight',
        'height',
        'length',
        'number',
        'internal_id',
        'total_charge_value', //amount
        'amount_usd',
        'unit',
        'width',
        'currency_id', //for shipping price
        'email_id',
        'seller_id',
        'country_id',
        'departure_id',
        'destination_id',
        'last_status_id',
        'used_contract_detail_id',
        'batch_id',
        'container_date',
        'last_container_id',
        'description', // by collector
        //'remark', // by client
        'tariff_type_id',
        'is_warehouse', // 0 - anbarda qebul edilmeyib; 1 - xarici anbarda; 2- xarici anbardan cixib (collector ilk scan edende 1 et ve yeni status yarat, flight close olanda 2 et ve status deyis)
        'received_by',
        'received_at',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
        'collected_by',
        'collected_at',
        'send_legality',
        'is_ok_custom',
        'carrier_status_id',
        'container_id',
        'last_container_id',
        'position_id',
        'partner_id',
        'delivered_by',
        'delivered_at',
        'last_status_date',
        'issued_to_courier_date',
        'partner_amount',
        'amount_azn',
        'external_w_debt',
        'external_w_debt_flag',
        'external_w_debt_day',
        'external_w_debt_azn',
        'internal_w_debt',
        'internal_w_debt_flag',
        'internal_w_debt_day',
        'internal_w_debt_usd',
        'branch_id',
        'package_img'
    ];

    protected static function boot()
    {
        parent ::boot();
        static ::addGlobalScope(new DeletedScope('package'));
    }

    public function item()
    {
        return $this->hasOne(Item::class, 'package_id', 'id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id', 'id');
    }

    public function collector()
    {
        return $this->hasOne(User::class, 'id', 'collected_by');
    }

    public function container()
    {
        return $this->belongsTo(Container::class, 'container_id', 'id');
    }

    public function status()
    {
        return $this->hasMany(PackageStatus::class, 'package_id', 'id');
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'id');
    }

    public function carrierLog()
    {   
        return $this->hasMany(PackageCarrierStatusTracking::class, 'internal_id', 'internal_id');
    }

}
