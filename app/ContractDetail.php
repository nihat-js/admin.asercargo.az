<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContractDetail extends Model
{
    protected $table = 'contract_detail';
    protected $fillable = [
        'contract_id',
        'type_id',
        'service_name',
        'title_az',
        'title_en',
        'title_ru',
        'description_az',
        'description_en',
        'description_ru',
        'country_id',
        'seller_id',
        'category_id',
        'from_weight',
        'to_weight',
        'weight_control',
        'rate',
        'charge',
        'currency_id',
        'destination_id',
        'departure_id',
        'start_date',
        'end_date',
        'calculate_type',
        'console_rate',
        'priority',
        'quantity_rate',
        'type',
        'is_active',
        'created_by',
        'deleted_by',
        'deleted_at'
    ];
}
