<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WaybillController extends Controller
{
    public function waybill_page(){
        return view('backend.waybill_page');
    }
    
    public function waybill($track)
    {
        $package = DB::table('package')
            ->leftJoin('item', 'package.id', '=', 'item.package_id')
            ->leftJoin('users', 'package.client_id', '=', 'users.id')
            ->leftJoin('locations', 'package.departure_id', '=', 'locations.id')
            ->leftJoin('container', 'package.last_container_id', '=', 'container.id')
            ->leftJoin('flight', 'container.flight_id', '=', 'flight.id')
            ->leftJoin('seller', 'package.seller_id', '=', 'seller.id')
            ->leftJoin('category', 'item.category_id', '=', 'category.id')
            ->whereRaw('(package.number LIKE "%' . $track . '%" or package.internal_id LIKE "%' . $track . '%")')
            ->select(
                'package.internal_id',
                'package.gross_weight',
                'package.volume_weight',
                'package.amount_usd',
                'package.carrier_registration_number as reg_number',
                DB::raw("CONCAT(users.name, ' ', users.surname) as full_name"),
                'users.address1 as user_address',
                'users.id as user_id',
                'users.phone1 as user_phone',
                'locations.address as location_address',
                'flight.awb',
                'flight.name as flight_name',
                'item.price_usd',
                'item.invoice_doc',
                'seller.name as seller_name',
                'flight.departure as departure',
                'flight.destination as destination',
                'item.quantity as quantity',
                'item.title as item_title',
                'category.name_en as cat_name'
            )
            ->first();
        
        if ($package == null){
            return view('backend.error');
        }
        
        return view('waybill', compact('package'));
    }
}
