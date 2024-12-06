<?php

namespace App\Http\Controllers;

use App\AWB;
use App\BalanceLog;
use App\ChangeAccountLog;
use App\Container;
use App\Contract;
use App\ContractDetail;
use App\Currency;
use App\ExchangeRate;
use App\Flight;
use App\Http\Controllers\Classes\Collector;
use App\Item;
use App\Location;
use App\Package;
use App\PackageCarrierStatusTracking;
use App\PackageStatus;
use App\Seller;
use App\Services\Carrier;
use App\Services\SendInternalPartnerService;
use App\Status;
use App\TariffType;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PackageController extends HomeController
{
    public function show()
    {   
        try {
            $search_arr = array(
                'number' => '',
                'seller' => '',
                'client' => '',
                'status' => '',
                'location' => '',
                'departure' => '',
                'destination' => '',
                'invoice_status' => ''
            );

            //$start = microtime(true);
            // dd("tr");
            $query = Item::leftJoin('package', 'item.package_id', '=', 'package.id')
                ->leftJoin('currency as item_currency', 'item.currency_id', '=', 'item_currency.id')
                ->join('currency as cur', 'package.currency_id', '=', 'cur.id')
                ->leftJoin('seller as s', 'package.seller_id', '=', 's.id')
                ->leftJoin('users as c', 'package.client_id', '=', 'c.id')
                ->join('lb_status as st', 'package.last_status_id', '=', 'st.id')
                ->leftJoin('position as p', 'package.position_id', '=', 'p.id')
                ->leftJoin('locations as l', 'p.location_id', '=', 'l.id')
                ->leftJoin('locations as dep', 'package.departure_id', '=', 'dep.id')
                ->leftJoin('filial', 'package.branch_id', '=', 'filial.id')
                //->leftJoin('locations as des', 'package.destination_id', '=', 'des.id')
                ->whereNull('package.deleted_by');

            if (!empty(Input::get('no')) && Input::get('no') != '' && Input::get('no') != null) {
                $where_no = Input::get('no');
                $query->where('package.id', $where_no);
                $search_arr['no'] = $where_no;
            }

            if (!empty(Input::get('number')) && Input::get('number') != '' && Input::get('number') != null) {
                $where_number = Input::get('number');
                $query->whereRaw('(package.number LIKE "%' . $where_number . '%" or package.internal_id LIKE "%' . $where_number . '%")');
                $search_arr['number'] = $where_number;
            }

            if (!empty(Input::get('client')) && Input::get('client') != '' && Input::get('client') != null) {
                $where_client = Input::get('client');
                $query->where('package.client_id', $where_client);
                $search_arr['client'] = $where_client;
            }

            if (!empty(Input::get('seller')) && Input::get('seller') != '' && Input::get('seller') != null) {
                $where_seller = Input::get('seller');
                $query->where('package.seller_id', $where_seller);
                $search_arr['seller'] = $where_seller;
            }

            if (!empty(Input::get('status')) && Input::get('status') != '' && Input::get('status') != null) {
                $where_status = Input::get('status');
                $query->where('package.last_status_id', $where_status);
                $search_arr['status'] = $where_status;
            }

            if (!empty(Input::get('location')) && Input::get('location') != '' && Input::get('location') != null) {
                $where_location = Input::get('location');
                if ($where_location == 'container') {
                    $query->whereNotNull('package.container_id');
                } else {
                    $query->where('p.location_id', $where_location);
                }
                $search_arr['location'] = $where_location;
            }

            if (!empty(Input::get('departure')) && Input::get('departure') != '' && Input::get('departure') != null) {
                $where_departure = Input::get('departure');
                $query->where('package.departure_id', $where_departure);
                $search_arr['departure'] = $where_departure;
            }

            if (!empty(Input::get('destination')) && Input::get('destination') != '' && Input::get('destination') != null) {
                $where_destination = Input::get('destination');
                $query->where('package.destination_id', $where_destination);
                $search_arr['destination'] = $where_destination;
            }

            if (!empty(Input::get('invoice_status')) && Input::get('invoice_status') != '' && Input::get('invoice_status') != null) {
                $where_invoice_status = Input::get('invoice_status');
                $search_arr['invoice_status'] = $where_invoice_status;

                switch ($where_invoice_status) {
                    case 'no_invoice':
                        {
                            $query->whereNull('item.invoice_doc');
                        }
                        break;
                    case 'not_confirmed':
                        {
                            $query->where('item.invoice_confirmed', 0);
                        }
                        break;
                    case 'confirmed':
                        {
                            $query->where('item.invoice_confirmed', 1);
                        }
                        break;
                }
            }

            //short by start
            $short_by = 'package.created_at';
            $shortType = 'DESC';
            if (!empty(Input::get('shortBy')) && Input::get('shortBy') != '' && Input::get('shortBy') != null) {
                $short_by = Input::get('shortBy');
            }
            if (!empty(Input::get('shortType')) && Input::get('shortType') != '' && Input::get('shortType') != null) {
                $short_type = Input::get('shortType');
                if ($short_type == 2) {
                    $shortType = 'DESC';
                } else {
                    $shortType = 'ASC';
                }
            }
            //short by finish
           // $start = microtime(true);
        if(Auth::user()->id == 150169){
            $packages = $query->orderBy($short_by, $shortType)
                ->select(
                    'package.id',
                    'package.last_status_id',
                    'package.number',
                    'package.height',
                    'package.width',
                    'package.length',
                    'package.gross_weight',
                    'package.unit',
                    'package.total_charge_value',
                    'package.paid',
                    'package.paid_status',
                    'package.carrier_status_id',
                    'package.carrier_registration_number',
                    'package.seller_id',
                    'package.other_seller',
                    'cur.name as currency',
                    's.name as seller',
                    'c.name as client_name',
                    'c.surname as client_surname',
                    'package.client_id',
                    'package.created_at',
                    'st.status_en as status',
                    'st.color as status_color',
                    'p.name as position',
                    'l.name as location',
                    'package.container_id as container',
                    'dep.name as departure',
                    //'des.name as destination',
                    'item.price',
                    'item_currency.name as invoice_currency',
                    'item.invoice_doc',
                    'filial.name as branch'
                )
                ->paginate(1);
        }else{
            if(Auth::user()->role() == 9){
                $packages = $query->orderBy($short_by, $shortType)
                ->whereDate('package.created_at', '>=', '2021-09-01')
                    ->select(
                        'package.id',
                        'package.last_status_id',
                        'package.number',
                        'package.height',
                        'package.width',
                        'package.length',
                        'package.gross_weight',
                        'package.unit',
                        'package.total_charge_value',
                        'package.paid',
                        'package.paid_status',
                        'package.carrier_status_id',
                        'package.carrier_registration_number',
                        'package.seller_id',
                        'package.other_seller',
                        'cur.name as currency',
                        's.name as seller',
                        'c.name as client_name',
                        'c.surname as client_surname',
                        'package.client_id',
                        'package.created_at',
                        'st.status_en as status',
                        'st.color as status_color',
                        'p.name as position',
                        'l.name as location',
                        'package.container_id as container',
                        'dep.name as departure',
                        //'des.name as destination',
                        'item.price',
                        'item_currency.name as invoice_currency',
                        'item.invoice_doc',
                        'filial.name as branch'
                    )
                    ->paginate(1);
            }else{
                $packages = $query->orderBy($short_by, $shortType)
                ->whereDate('package.created_at', '>=', '2021-09-01')
                ->select(
                    'package.id',
                    'package.last_status_id',
                    'package.number',
                    'package.height',
                    'package.width',
                    'package.length',
                    'package.gross_weight',
                    'package.unit',
                    'package.total_charge_value',
                    'package.paid',
                    'package.paid_status',
                    'package.carrier_status_id',
                    'package.carrier_registration_number',
                    'package.seller_id',
                    'package.other_seller',
                    'cur.name as currency',
                    's.name as seller',
                    'c.name as client_name',
                    'c.surname as client_surname',
                    'package.client_id',
                    'package.created_at',
                    'st.status_en as status',
                    'st.color as status_color',
                    'p.name as position',
                    'l.name as location',
                    'package.container_id as container',
                    'dep.name as departure',
                    //'des.name as destination',
                    'item.price',
                    'item_currency.name as invoice_currency',
                    'item.invoice_doc',
                    'filial.name as branch'
                )
                ->paginate(50);
            }
        }
            
              //$end = microtime(true);

            //dd(($end-$start)*1000);
            $sellers = Seller::whereNull('deleted_by')->where('only_collector', 0)->select('id', 'name')->get();
            $statuses = Status::whereNull('deleted_by')->select('id', 'status_en as status')->get();
            $tariff_types = TariffType::whereNull('deleted_by')->select('id', 'name_en as name')->get();
            $locations = Location::whereNull('deleted_by')->select('id', 'name')->get();

            $branches = DB::table("filial")->where("is_active",1)->get();

            // dd($locations);
            return view('backend.packages', compact(
                'packages',
                'sellers',
                'statuses',
                'locations',
                'tariff_types',
                'search_arr',
                "branches",
            ));
        } catch (\Exception $exception) {
            //dd($exception);
            return view('backend.error');
        }
    }

    public function delete(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Id not found!']);
        }
        try {
            Package::where(['id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);
            Item::where(['package_id'=>$request->id])->whereNull('deleted_by')->update(['deleted_by'=>Auth::id(), 'deleted_at'=>Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }



    public function set_package_declared_status(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Package not found']);
        }
        try {
            PackageStatus::create([
                'status_id' => 11, //declared
                'package_id' => $request->id,
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function show_status_history_for_package(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Package not found']);
        }
        try {
            $package_id = $request->id;

            $statuses = PackageStatus::leftJoin('lb_status as status', 'package_status.status_id', '=', 'status.id')
                ->leftJoin('users', 'package_status.created_by', '=', 'users.id')
                ->where('package_status.package_id', $package_id)
                ->select(
                    'status.status_en as status',
                    'package_status.created_by as suite',
                    'users.name as user_name',
                    'users.surname as user_surname',
                    'package_status.created_at as date'
                )
                ->get();

            if (count($statuses) == 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Status not found!']);
            }

            return response(['case' => 'success', 'title' => 'Success!', 'statuses' => $statuses]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function change_client(Request $request) {
        $validator = Validator::make($request->all(), [
            'client_id' => ['required', 'integer'],
            'package_id' => ['required', 'integer'],
            'remark' => ['nullable', 'string', 'max:500'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Package Or Client not found']);
        }
        try {
            $package = Package::where('package.id', $request->package_id)
                ->leftJoin('lb_status as status', 'package.last_status_id', '=', 'status.id')
                ->where('package.paid_status', 0)
                ->whereNull('package.delivered_by')
                ->whereNull('package.issued_to_courier_date')
                ->whereNull('package.deleted_by')
                ->select([ 'package.number', 'package.client_id', 'package.last_status_id', 'status.status_en', 'package.carrier_status_id'])
                ->first();

            if (!$package) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Package not found (Package paid or delivered or issued to courier)!']);
            }
            if ($package->client_id == $request->client_id) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'The old and new clients are the same!']);
            }
            if (in_array($package->last_status_id, [5, 14, 38, 39, 40])) {
                return response([
                    'case' => 'error',
                    'title' => 'Oops!',
                    'content' => 'Can not change client in status ' . $package->status_en
                ]);
            }

            $status_id = 37;
            $customer_type_id = 1;
            $carrier_status_id = 9;



            if($request->client_id != 0){
                $client = User::where('id', $request->client_id)->first();
                if($client->is_legality == 1){
                    $status_id = 41;
                    $customer_type_id = 2;
                    $carrier_status_id = 0;
                }
            }


            $partnerService = new SendInternalPartnerService();

            if($package->carrier_status_id == 8)
            {
                Package::where('id', $request->package_id)
                    ->update([
                        'client_id' => $request->client_id
                    ]);

                $partnerService->ChangePackageUser($package->number, $request->client_id);

            }
            elseif($package->carrier_status_id == 1 || $package->carrier_status_id == 2 || $package->carrier_status_id == 3 || $package->carrier_status_id == 7)
            {
                return response([
                    'case' => 'error',
                    'title' => 'Oops!',
                    'content' => 'Can not change client'
                ]);
            }
            else
            {
                Package::where('id', $request->package_id)
                ->update([
                    'client_id' => $request->client_id,
                    'last_status_id' => $status_id,
                    'customer_type_id' => $customer_type_id,
                    'carrier_status_id' => $carrier_status_id
                ]);

                $partnerService->ChangePackageUser($package->number, $request->client_id);

            }

            // Package::where('id', $request->package_id)
            // ->whereNotIn('carrier_status_id', [1, 2, 3, 7, 8])
            // ->update([
            //     'client_id' => $request->client_id,
            //     'carrier_status_id' => 9
            // ]);

            ChangeAccountLog::create([
                'old_client_id' => $package->client_id,
                'new_client_id' => $request->client_id,
                'package_id' => $request->package_id,
                'remark' => $request->remark,
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Client is changed!']);
        } catch (\Exception $exception) {
            //dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function change_branch(Request $request){
        // dd($request->all());
        // return response()->json($request->all());   
        $validator = Validator::make($request->all(), [
            'branch_id' => ['required', 'integer'],
            'package_id' => ['required', 'integer'],
        ]);


        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Package Or Branch not found']);
        }
        try {
            $package = Package::where('package.id', $request->package_id)
                ->leftJoin('lb_status as status', 'package.last_status_id', '=', 'status.id')
                // ->where('package.paid_status', 0)
                // ->whereNull('package.delivered_by')
                // ->whereNull('package.issued_to_courier_date')
                ->whereNull('package.deleted_by')
                ->select([ 'package.number', 'package.branch_id', 'package.last_status_id', 'status.status_en', 'package.carrier_status_id'])
                ->first();

            if (!$package) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Package not found (Package paid or delivered or issued to courier)!']);
            }
            if ($package->branch_id == $request->branch_id) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'The old and new branches are the same!']);
            }
            if (in_array($package->last_status_id, [5, 14, 38, 39, 40])) {
                return response([
                    'case' => 'error',
                    'title' => 'Oops!',
                    'content' => 'Can not change branch in status ' . $package->status_en
                ]);
            }

            Package::where('id', $request->package_id)
                ->update([
                    'branch_id' => $request->branch_id
                ]);

                DB::insert('insert into change_package_branch_log (package_id, old_branch_id, new_branch_id, user_id) values (?, ?, ?, ?)', [
                    $request->package_id,
                    $package->branch_id,
                    $request->branch_id,
                    Auth::user()->id,
                  
                ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Branch is changed!']);
        } catch (\Exception $exception) {
            // dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }

        
    }

    public function change_weight(Request $request, Carrier $carrier) {
        $validator = Validator::make($request->all(), [
            'package_id' => ['required', 'integer'],
            'gross_weight' => ['required'],
            'chargeable_weight' => ['required', 'integer'],
            'tariff_type_id' => ['required', 'integer'],
            'length' => ['nullable', 'integer'],
            'height' => ['nullable', 'integer'],
            'width' => ['nullable', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Gross weight is required!']);
        }
        try {
            DB::beginTransaction();
            $date = Carbon::now();
            $rates = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
                ->select('rate', 'from_currency_id', 'to_currency_id')
                ->get();

            if (count($rates) == 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rates not found!']);
            }

            $package_id = $request->package_id;
            $chargeable_weight = $request->chargeable_weight; // 1 - default; 2 - gross; 3 - volume
            $type = $request->tariff_type_id;
            $gross_weight = $request->gross_weight;
            $length = $request->length;
            $height = $request->height;
            $width = $request->width;
            $volume_weight = ($width * $height * $length) / 6000;

            $package = Item::leftJoin('package', 'item.package_id', '=', 'package.id')
                ->leftJoin('lb_status as status', 'package.last_status_id', '=', 'status.id')
                ->where('item.package_id', $package_id)
                ->whereNull('package.deleted_by')
                ->whereNull('package.delivered_by')
                ->with('statuses')
                ->select(
                    'package.id as package_id',
                    'package.client_id',
                    'package.departure_id',
                    'package.destination_id',
                    'package.gross_weight',
                    'package.volume_weight',
                    'package.internal_id',
                    'package.carrier_status_id',
                    'package.length',
                    'package.width',
                    'package.height',
                    'package.tariff_type_id',
                    'package.seller_id',
                    'item.category_id',
                    'package.paid_status',
                    'package.paid',
                    'package.total_charge_value as amount',
                    'package.discounted_amount',
                    'package.currency_id',
                    'package.last_status_id',
		    'package.in_baku',
                    'status.status_en'
                )
                ->first();

            if (!$package) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Package not found or package delivered!']);
            }
            if (count($package->statuses)) {
                if (count($package->statuses->where('status_id', 5)) and $package->in_baku != 1) {
                    return response([
                        'case' => 'error',
                        'title' => 'Oops!',
                        'content' => 'Can not change weight when package status is: Ready for carriage'
                    ]);
                }
            }

            $client_id = $package->client_id;
            $departure_id = $package->departure_id;
            $destination_id = $package->destination_id;
            $category_id = $package->category_id;
            $seller_id = $package->seller_id;
            if ($type != 0) {
                $tariff_type_id = $type;
            } else {
                $tariff_type_id = $package->tariff_type_id;
            }

            $old_paid_status = $package->paid_status;
            $old_amount = $package->amount;
            //$old_paid = $package->paid;
            $old_discounted_amount = $package->discounted_amount;
            $old_real_paid = $old_amount - $old_discounted_amount;
            $old_currency_id = $package->currency_id;

            $rate_to_usd_for_old_amount = $this->calculate_exchange_rate($rates, $old_currency_id, 1);

            if ($rate_to_usd_for_old_amount == 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'warning', 'content' => 'Exchange rate not found (' . $old_currency_id . ' -> 1)!']);
            }

            // $collector = new CollectorController();
            $amount_response = $this->calculate_amount($client_id, $departure_id, $destination_id, $category_id, $seller_id, $gross_weight, $volume_weight, $length, $width, $height, $tariff_type_id, $chargeable_weight);

            if ($amount_response['type'] == false) {
                return response(['case' => 'error', 'title' => 'Error!', 'type' => 'error', 'content' => 'Calculate amount:' . $amount_response['response']]);
            }

            $amount = $amount_response['amount'];
            $currency_id_for_amount = $amount_response['currency_id'];
            $chargeable_weight_type = $amount_response['chargeable_weight_type'];
            $used_contract_detail_id = $amount_response['used_contract_detail_id'];

            if ($currency_id_for_amount == 1) {
                //usd
                $amount_usd = $amount;
            } else {
                $rate_to_usd_for_new_amount = $this->calculate_exchange_rate($rates, $currency_id_for_amount, 1);

                if ($rate_to_usd_for_new_amount != 0) {
                    $amount_usd = $rate_to_usd_for_new_amount * $amount;
                    $amount_usd = sprintf('%0.2f', $amount_usd);
                } else {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'warning', 'content' => 'Exchange rate not found (' . $currency_id_for_amount . ' -> 1)!']);
                }
            }
            
            $rateto_azn = $this->calculate_exchange_rate($rates, $currency_id_for_amount, 3);
    
            if ($rateto_azn != 0) {
                $amount_azn = $rateto_azn * $amount;
                $amount_azn = sprintf('%0.2f', $amount_azn);
            } else {
                return response(['case' => 'warning', 'title' => 'Oops!', 'type' => 'warning', 'content' => 'Exchange rate not found (' . $currency_id_for_amount . ' -> 1)!']);
            }
            //dd($amount_azn);

            $package_update_arr = array();
            $package_update_arr['chargeable_weight'] = $chargeable_weight_type;
            $package_update_arr['total_charge_value'] = $amount;
            $package_update_arr['amount_usd'] = $amount_usd;
            $package_update_arr['amount_azn'] = $amount_azn;
            $package_update_arr['currency_id'] = $currency_id_for_amount;
            $package_update_arr['used_contract_detail_id'] = $used_contract_detail_id;
            $package_update_arr['gross_weight'] = $gross_weight;
            $package_update_arr['volume_weight'] = $volume_weight;
            $package_update_arr['length'] = $length;
            $package_update_arr['height'] = $height;
            $package_update_arr['width'] = $width;

            if ($old_paid_status == 1) {
                $client = User::where('id', $client_id)->select('balance')->first();
                if (!$client) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'User not found!']);
                }
                $client_old_balance = $client->balance;

                $old_amount_usd = $rate_to_usd_for_old_amount * $old_amount;
                $old_amount_usd = sprintf('%0.2f', $old_amount_usd);

                if ($amount_usd > $old_amount_usd) {
                    // set to unpaid
                    // baglamani ondenilmedi et, odenilmis meblegi musterinin balansina at
                    $package_update_arr['paid_status'] = 0;
                    $package_update_arr['paid'] = 0;
                    $package_update_arr['discounted_amount'] = 0;
                    $package_update_arr['promo_code'] = null;

                    $rate_to_azn_for_old_amount = $this->calculate_exchange_rate($rates, $old_currency_id, 3);

                    $old_real_paid_usd = $rate_to_usd_for_old_amount * $old_real_paid;
                    $old_real_paid_usd = sprintf('%0.2f', $old_real_paid_usd);

                    $old_real_paid_azn = $rate_to_azn_for_old_amount * $old_real_paid;
                    $old_real_paid_azn = sprintf('%0.2f', $old_real_paid_azn);

                    $payment_code = Str::random(20);
                    BalanceLog::create([
                        'payment_code' => $payment_code,
                        'amount' => $old_real_paid_usd,
                        'amount_azn' => $old_real_paid_azn,
                        'client_id' => $client_id,
                        'status' => 'in',
                        'type' => 'back',
                        'created_by' => Auth::id()
                    ]);

                    $client_new_balance = $client_old_balance + $old_real_paid_usd;

                    User::where('id', $client_id)->update(['balance' => $client_new_balance]);
                } else if ($amount_usd < $old_amount_usd) {
                    // artiq meblegi musteri hesabina yaz
                    $remaining_amount_usd = $old_amount_usd - $amount_usd;

                    $rate_usd_to_azn = $this->calculate_exchange_rate($rates, 1, 3);

                    $remaining_amount_azn = $rate_usd_to_azn * $remaining_amount_usd;
                    $remaining_amount_azn = sprintf('%0.2f', $remaining_amount_azn);

                    $payment_code = Str::random(20);
                    BalanceLog::create([
                        'payment_code' => $payment_code,
                        'amount' => $remaining_amount_usd,
                        'amount_azn' => $remaining_amount_azn,
                        'client_id' => $client_id,
                        'status' => 'in',
                        'type' => 'back',
                        'created_by' => Auth::id()
                    ]);

                    $client_new_balance = $client_old_balance + $remaining_amount_usd;

                    User::where('id', $client_id)->update(['balance' => $client_new_balance]);
                }
            }

            if (
                $package->package_id
                and ($package->carrier_status_id != 0)
                and !in_array($package->carrier_status_id, [1, 2, 3, 7, 8])
            ) {
                $deleteResponse = $carrier->destroyPackage($package->package_id);
                if ($deleteResponse['deleted']) {
                    PackageCarrierStatusTracking::create([
                        'package_id' => $package->package_id,
                        'internal_id' => $package->internal_id,
                        'carrier_status_id' => 0,
                        'carrier_registration_number' => null,
                        'note' => 'deleted_from_customs',
                    ]);
                    PackageStatus::create([
                        'package_id' => $package->package_id,
                        'status_id' => 37,
                        'created_by' => Auth::id()
                    ]);
                    $package_update_arr['carrier_status_id'] = 0;
                    $package_update_arr['carrier_registration_number'] = 3;
                }
            }

            Package::where('id', $package_id)->update($package_update_arr);

            DB::commit();
            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Weight is changed! New amount: ' . $amount]);
        } catch (\Exception $exception) {
            // dd($exception);
            DB::rollBack();
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function manifest_collector()
    {
        try {

            if(Auth::user()->id == 137297){
                $flights = Flight::whereNull('deleted_by')
                    ->whereRaw('(public = 1 or location_id = ?)', Auth::user()->location())
                    ->whereDate('created_at', '>', date('2021-08-20'))
                    ->select('id', 'name')
                    ->orderBy('id', 'DESC')
                    ->take(100)
                    ->get();

                $containers = Container::whereNull('deleted_by')
                    ->whereRaw('(public = 1 or departure_id = ?)', Auth::user()->location())
                    ->whereDate('created_at', '>', date('2021-08-20'))
                    ->select('id')
                    ->orderBy('id', 'DESC')
                    ->take(100)
                    ->get();
            }else{
                $flights = Flight::whereNull('deleted_by')
                    ->whereRaw('(public = 1 or location_id = ?)', Auth::user()->location())
                    ->select('id', 'name')
                    ->orderBy('id', 'DESC')
                    ->take(100)
                    ->get();
                $containers = Container::whereNull('deleted_by')
                    ->whereRaw('(public = 1 or departure_id = ?)', Auth::user()->location())
                    ->select('id')
                    ->orderBy('id', 'DESC')
                    ->take(100)
                    ->get();
            }



            $query = Item::LeftJoin('package', 'item.package_id', '=', 'package.id')
                ->leftJoin('currency as cur', 'item.currency_id', '=', 'cur.id')
                ->leftJoin('seller as s', 'package.seller_id', '=', 's.id')
                ->leftJoin('users as c', 'package.client_id', '=', 'c.id')
                ->leftJoin('category as cat', 'item.category_id', '=', 'cat.id')
                ->leftJoin('container as con', 'package.container_id', '=', 'con.id')
                ->leftJoin('custom_category', 'item.custom_cat_id', '=', 'custom_category.id')
                ->whereNotNull('package.container_id')
                ->where('package.departure_id', Auth::user()->location())
                ->whereNull('item.deleted_by')
                ->whereNull('package.deleted_by');

            $search_arr = array(
                'flight' => '',
                'container' => '',
            );

            $where_flight = false;
            $where_container = false;
            $container = false;
            $selected_flight = false;

            if (!empty(Input::get('container')) && Input::get('container') != '' && Input::get('container') != null) {
                $where_container = Input::get('container');
                $query->where('package.container_id', $where_container);
                $search_arr['container'] = $where_container;

                $container = Container::where('id', $where_container)->select('flight_id', 'awb_id')->first();
            }

            if ($where_container != false && $container != false) {
                $where_flight = $container->flight_id;
                $query->where('con.flight_id', $where_flight);
                $search_arr['flight'] = $where_flight;
            } else {
                if (!empty(Input::get('flight')) && Input::get('flight') != '' && Input::get('flight') != null) {
                    $where_flight = Input::get('flight');
                    $query->where('con.flight_id', $where_flight);
                    $search_arr['flight'] = $where_flight;
                }
            }

            if ($where_flight == false && $where_container == false) {
                return view('backend.packages_collector')->with([
                    'packages' => false,
                    'search_arr' => $search_arr,
                    'flights' => $flights,
                    'containers' => $containers,
                    'selected_flight' => $selected_flight
                ]);
            }

            //short by start
            $short_by = 'package.id';
            $shortType = 'DESC';
            if (!empty(Input::get('shortBy')) && Input::get('shortBy') != '' && Input::get('shortBy') != null) {
                $short_by = Input::get('shortBy');
            }
            if (!empty(Input::get('shortType')) && Input::get('shortType') != '' && Input::get('shortType') != null) {
                $short_type = Input::get('shortType');
                if ($short_type == 2) {
                    $shortType = 'DESC';
                } else {
                    $shortType = 'ASC';
                }
            }
            //short by finish

            $packages = $query
                ->orderBy($short_by, $shortType)
                ->select(
                    'package.number',
                    'package.client_id',
                    'package.seller_id',
                    'package.other_seller',
                    'c.suite',
                    'c.name as client_name',
                    'c.surname as client_surname',
                    'c.address1 as client_address',
                    'package.gross_weight',
                    's.name as seller',
                    'item.price',
                    'item.subCat',
                    'item.title',
                    'cur.name as currency',
                    'item.quantity',
                    'cat.name_en as category',
                    'custom_category.goodsNameEn'
                )
                ->get();

            $selected_awb = '';
            if ($where_flight != false) {
                $flight = Flight::where('id', $where_flight)->select('carrier', 'flight_number', 'awb', 'plan_take_off', 'departure', 'destination')->first();
                if ($flight) {
                    $selected_flight = $flight;

                    if ($flight->awb != null && strlen($flight->awb) > 0) {
                        $selected_awb = 'AWB ' . $flight->awb;
                    }
                }
            }

            return view('backend.packages_collector')->with([
                'packages' => $packages,
                'search_arr' => $search_arr,
                'flights' => $flights,
                'containers' => $containers,
                'selected_flight' => $selected_flight,
                'selected_awb' => $selected_awb
            ]);
        } catch (\Exception $exception) {
            //dd($exception);
            return view('backend.error');
        }
    }

    public function collector_search_packages() {
        try {
            $query = Item::LeftJoin('package', 'item.package_id', '=', 'package.id')
                ->leftJoin('currency as cur', 'item.currency_id', '=', 'cur.id')
                ->leftJoin('seller as s', 'package.seller_id', '=', 's.id')
                ->leftJoin('category as cat', 'item.category_id', '=', 'cat.id')
                ->leftJoin('position as pos', 'package.position_id', '=', 'pos.id')
                ->leftJoin('lb_status as status', 'package.last_status_id', '=', 'status.id')
                ->where('package.departure_id', Auth::user()->location())
                ->whereNull('item.deleted_by')
                ->whereNull('package.deleted_by');

            $has_search_value = false;
            $client_id = '';
            $track = '';

            if (!empty(Input::get('client')) || Input::get('client') != '' || Input::get('client') != null) {
                $has_search_value = true;
                $client_id = Input::get('client');
                $query->where('package.client_id', $client_id);
            }

            if (!empty(Input::get('track')) || Input::get('track') != '' || Input::get('track') != null) {
                $has_search_value = true;
                $track = Input::get('track');
                $query->whereRaw("(package.number = '" . $track . "' or package.internal_id = '" . $track . "')");
            }

            if ($has_search_value == false) {
                return view('backend.collector.search_packages')->with([
                    'packages' => false,
                    'client' => $client_id,
                    'track' => $track
                ]);
            }

            //short by start
            $short_by = 'package.id';
            $shortType = 'DESC';
            if (!empty(Input::get('shortBy')) && Input::get('shortBy') != '' && Input::get('shortBy') != null) {
                $short_by = Input::get('shortBy');
            }
            if (!empty(Input::get('shortType')) && Input::get('shortType') != '' && Input::get('shortType') != null) {
                $short_type = Input::get('shortType');
                if ($short_type == 2) {
                    $shortType = 'DESC';
                } else {
                    $shortType = 'ASC';
                }
            }
            //short by finish

            $packages = $query
                ->orderBy($short_by, $shortType)
                ->select(
                    'package.id',
                    'package.number',
                    'package.internal_id',
                    'status.status_en as status',
                    'package.container_id',
                    'package.position_id',
                    'pos.name as position',
                    'package.gross_weight',
                    's.name as seller',
                    'cat.name_en as category',
                    'item.price',
                    'cur.name as currency',
                    'item.invoice_doc',
                    'item.invoice_uploaded_date'
                )
                ->paginate(50);

            return view('backend.collector.search_packages')->with([
                'packages' => $packages,
                'client' => $client_id,
                'track' => $track
            ]);
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function collector_delete_package(Request $request) {
        return false;
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Package ID not found!']);
        }
        try {
            $id = $request->id;

            $package = Package::where('id', $id)
                ->where('package.departure_id', Auth::user()->location())
                ->select('is_warehouse', 'in_baku')
                ->first();

            if (!$package) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Package not found at your location!']);
            }

            if ($package->is_warehouse != 1 || $package->in_baku != 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Packages can only be deleted in foreign warehouse!']);
            }

            Package::where(['id' => $id, 'is_warehouse' => 1, 'in_baku' => 0])->update(['deleted_by' => Auth::id(), 'deleted_at' => Carbon::now()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'id'=>$request->id]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

    private function calculate_exchange_rate($rates, $from, $to)
    {
        try {
            if ($from == $to) {
                return 1;
            }

            foreach ($rates as $rate) {
                if ($rate->from_currency_id == $from && $rate->to_currency_id == $to) {
                    return $rate->rate;
                }
            }

            return 0;
        } catch (\Exception $exception) {
            return 0;
        }
    }

    public function delete_from_customs(Request $request, Carrier $carrier)
    {
        try {
            DB::beginTransaction();
            $package = Package::with('client')
                ->where('id', $request->get('id'))
                ->first();
            $isDeleted = false;
            if ($package) {
                if (!$package->client->passport_fin) {
                    return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Customer FIN is null']);
                }
                $deleteResponse = $carrier->destroyPackage($package->id);
                if ($deleteResponse['deleted']) {
                    PackageCarrierStatusTracking::create([
                        'package_id' => $package->id,
                        'internal_id' => $package->internal_id,
                        'carrier_status_id' => 0,
                        'carrier_registration_number' => null,
                        'note' => 'deleted_from_customs',
                    ]);
                    PackageStatus::create([
                        'package_id' => $package->id,
                        'status_id' => 37,
                        'created_by' => Auth::id()
                    ]);
                    Package::where('id', $package->id)->update([
                        'carrier_status_id' => 0,
                        'carrier_registration_number' => 2
                    ]);

                    $isDeleted = true;
                }
                DB::commit();
                if ($isDeleted) {
                    return response([
                        'case' => 'success',
                        'title' => 'Success!',
                        'content' => 'Package deleted from Customs!'
                    ]);
                }
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('customs_delete_fail', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode()
            ]);
        }

        return response([
            'case' => 'warning',
            'title' => 'Smart Customs!',
            'content' => 'Not responding'
        ]);
    }


    public function calculate_amount($client_id, $departure, $destination, $category_id, $seller_id, $gross_weight, $volume_weight, $length, $width, $height, $tariff_type_id, $has_chargeable = 1)
	{
		try {
			$current_date = Carbon::today();
			$def = false;
			$contract_id = 0;
			$chargeable_weight = 0;
			$amount = 0;
			$currency_name = '';
			$currency_id = 0;
			$used_detail_id = 0;
			$chargeable_weight_type = 1; // 1 - gross; 2 - volume

			$client_contract = User::where('id', $client_id)->whereNull('deleted_by')->select('contract_id')->first();
			if ($client_contract) {
				if ($client_contract->contract_id != null) {
					$contract_id = $client_contract->contract_id;
					if (Contract::where('id', $contract_id)->where('start_date', '<=', $current_date)->where('end_date', '>=', $current_date)->where('is_active', 1)->whereNull('deleted_by')->count() == 0) {
						//default contract
						$def = true;
					}
					
				} else {
					//default contract
					$def = true;
				}
			
			} else {
				//default contract
				$def = true;
			}
			
			//get default contract
			if ($def) {
				$default_contract = Contract::where('default_option', 1)->where('is_active', 1)->where('start_date', '<=', $current_date)->where('end_date', '>=', $current_date)->whereNull('deleted_by')->orderBy('id', 'desc')->select('id')->first();
				if ($default_contract) {
					$contract_id = $default_contract->id;
				} else {
					$contract_id = 0;
				}
			}
			
			//contract exists control
			if ($contract_id == 0) {
				return ['type' => false, 'response' => 'No valid contract found 0!'];
			}

			//get contract details
			$details = ContractDetail::where(['contract_id' => $contract_id, 'type_id' => $tariff_type_id])->whereNull('deleted_by')
					->where('start_date', '<=', $current_date)
					->where('end_date', '>=', $current_date)
                    ->where(function ($query) use ($seller_id) {
                        $query->where('seller_id', null);
                        $query->orWhere('seller_id', $seller_id);
                    })
                    ->where(function ($query) use ($category_id) {
                        $query->where('category_id', null);
                        $query->orWhere('category_id', $category_id);
                    })
					->where(['departure_id' => $departure, 'destination_id' => $destination])
					->select('id', 'seller_id', 'category_id', 'weight_control', 'from_weight', 'to_weight', 'rate', 'charge', 'currency_id')
					->get();
					// dd($details);
			if (count($details) == 0) {
				// dd($details);
				if ($def) {
					// dd($def);
					return ['type' => false, 'response' => 'No valid rate found 0!'];
				} else {
					//get default contract
					$default_contract = Contract::where('default_option', 1)->where('is_active', 1)->where('start_date', '<=', $current_date)->where('end_date', '>=', $current_date)->whereNull('deleted_by')->orderBy('id', 'desc')->select('id')->first();
					if ($default_contract) {
						$contract_id = $default_contract->id;
						
						$details = ContractDetail::where(['contract_id' => $contract_id, 'type_id' => $tariff_type_id])->whereNull('deleted_by')
								->where('start_date', '<=', $current_date)
								->where('end_date', '>=', $current_date)
								->where(['departure_id' => $departure, 'destination_id' => $destination])
                                ->where(function ($query) use ($seller_id) {
                                    $query->where('seller_id', null);
                                    $query->orWhere('seller_id', $seller_id);
                                })
                                ->where(function ($query) use ($category_id) {
                                    $query->where('category_id', null);
                                    $query->orWhere('category_id', $category_id);
                                })
								->select('id', 'seller_id', 'category_id', 'weight_control', 'from_weight', 'to_weight', 'rate', 'charge', 'currency_id')
								->get();
								// dd($details);
						if (count($details) == 0) {
							return ['type' => false, 'response' => 'No valid rate found 1!'];
						}
					} else {
						return ['type' => false, 'response' => 'No valid contract found 1!'];
					}
				}
			}

			$rates = array();
			$rate_count = 0;
			$choose_details = $this->choose_details($details, $volume_weight, $length, $width, $height, $gross_weight, $category_id, $seller_id, $has_chargeable);
			$rates = $choose_details['rates'];
			$rate_count = $choose_details['rate_count'];
			$chargeable_weight_type = $choose_details['chargeable_weight_type'];
			$chargeable_weight = $choose_details['chargeable_weight'];

			if ($rate_count == 0) {
				if ($def) {
					return ['type' => false, 'response' => 'No valid rate found!'];
				} else {
					//get default contract
					$default_contract = Contract::where('default_option', 1)->where('is_active', 1)->where('start_date', '<=', $current_date)->where('end_date', '>=', $current_date)->whereNull('deleted_by')->orderBy('id', 'desc')->select('id')->first();
					if ($default_contract) {
						$contract_id = $default_contract->id;
						$details = ContractDetail::where(['contract_id' => $contract_id, 'type_id' => $tariff_type_id])->whereNull('deleted_by')
								->where('start_date', '<=', $current_date)
								->where('end_date', '>=', $current_date)
								->where(['departure_id' => $departure, 'destination_id' => $destination])
                                ->where(function ($query) use ($seller_id) {
                                    $query->where('seller_id', null);
                                    $query->orWhere('seller_id', $seller_id);
                                })
                                ->where(function ($query) use ($category_id) {
                                    $query->where('category_id', null);
                                    $query->orWhere('category_id', $category_id);
                                })
								->select('id', 'seller_id', 'category_id', 'weight_control', 'from_weight', 'to_weight', 'rate', 'charge', 'currency_id')
								->get();

						if (count($details) == 0) {
							return ['type' => false, 'response' => 'No valid rate found 2!'];
						}

						$rates = array();
						$rate_count = 0;
						$choose_details = $this->choose_details($details, $volume_weight, $length, $width, $height, $gross_weight, $category_id, $seller_id, $has_chargeable);
						$rates = $choose_details['rates'];
						$rate_count = $choose_details['rate_count'];
						$chargeable_weight_type = $choose_details['chargeable_weight_type'];
						$chargeable_weight = $choose_details['chargeable_weight'];

						if ($rate_count == 0) {
							return ['type' => false, 'response' => 'No valid rate found 3! '];
						}
					} else {
						return ['type' => false, 'response' => 'No valid contract found 2!'];
					}
				}
			}

			//sort rates
			$rates = collect($rates)->sortBy('id')->reverse()->toArray();
			$rates = collect($rates)->sortBy('priority')->reverse()->toArray();

			$selected_rate = false;
			$rate_first = false;
			foreach ($rates as $rate) {
				if ($rate_first == false) {
					$selected_rate = $rate;
					$rate_first = true;
				} else {
					break;
				}
			}

			if ($selected_rate == false) {
				return ['type' => false, 'response' => 'No valid rate found 4!'];
			} else {
				$selected_rate_id = $selected_rate['id'];
			}

			foreach ($details as $detail) {
				if ($detail->id == $selected_rate_id) {
					$used_detail_id = $detail->id;
					$rate_value = $detail->rate;
					$charge = $detail->charge;
					$currency_id = $detail->currency_id;
					$currency = Currency::where('id', $currency_id)->select('name')->first();
					if ($currency) {
						$currency_name = $currency->name;
					}

					$amount = ($chargeable_weight * $rate_value) + $charge;
					break;
				}
			}

			$amount = number_format((float)$amount, 2, '.', '');

			return ['type' => true, 'amount' => $amount, 'currency' => $currency_name, 'currency_id' => $currency_id, 'chargeable_weight_type' => $chargeable_weight_type, 'used_contract_detail_id' => $used_detail_id];
		} catch (\Exception $exception) {
            // dd($exception);
			return ['type' => false, 'response' => 'Something went wrong when contract selected!'];
		}
	}


    private function choose_details($details, $volume_weight, $length, $width, $height, $gross_weight, $category_id, $seller_id, $has_chargeable = 1)
	{
		$rates = array();
		$rate_count = 0;
		$chargeable_weight = 0;
		$chargeable_weight_type = 1; // 1 - gross; 2 - volume
		$i = 0;

		foreach ($details as $detail) {
			if ($has_chargeable === 2) {
				//gross weight
				$chargeable_weight = $gross_weight;
				$chargeable_weight_type = 1;
			} else if ($has_chargeable === 3) {
				//volume weight
				$chargeable_weight = $volume_weight;
				$chargeable_weight_type = 2;
			} else {
				//default
				if ($detail->weight_control === 1) {
					if ($length > 0 && $width > 0 && $height > 0) {
						if ($volume_weight > $gross_weight) {
							$chargeable_weight = $volume_weight;
							$chargeable_weight_type = 2;
						} else {
							$chargeable_weight = $gross_weight;
							$chargeable_weight_type = 1;
						}
					} else {
						$chargeable_weight = $gross_weight;
						$chargeable_weight_type = 1;
					}
				} else {
					$chargeable_weight = $gross_weight;
					$chargeable_weight_type = 1;
				}
			}


			if (($chargeable_weight >= $detail->from_weight) && ($chargeable_weight <= $detail->to_weight)) {
				//ok
				if ($detail->seller_id == null && $detail->category_id == null) {
					//no seller and no category
					//priority = 0
					$rate_count++;
					$rates[$rate_count]['id'] = $detail->id;
					$rates[$rate_count]['priority'] = 0;
				}
				if ($detail->seller_id == null && $detail->category_id != null) {
					//only category
					//priority = 1
					if ($detail->category_id == $category_id) {
						$rate_count++;
						$rates[$rate_count]['id'] = $detail->id;
						$rates[$rate_count]['priority'] = 1;
					}
				}
				if ($detail->category_id == null && $detail->seller_id != null) {
					//only seller
					//priority = 2
					if ($detail->seller_id == $seller_id) {
						$rate_count++;
						$rates[$rate_count]['id'] = $detail->id;
						$rates[$rate_count]['priority'] = 2;
					}
				}
				if ($detail->seller_id != null && $detail->category_id != null) {
					//seller and category
					//priority = 3
					if ($detail->seller_id == $seller_id && $detail->category_id == $category_id) {
						$rate_count++;
						$rates[$rate_count]['id'] = $detail->id;
						$rates[$rate_count]['priority'] = 3;
					}
				}
			} else {
				continue;
			}
		}

		return ['rates' => $rates, 'rate_count' => $rate_count, 'chargeable_weight_type' => $chargeable_weight_type, 'chargeable_weight' => $chargeable_weight];
	}


    public function collector_packages() {
        try {
           
            $search_status = 40;
            //dd($statuses);
            $user_location = Auth::user()->location();
            // $query = DB::select( DB::raw(
            //     "SELECT 
            //         package.id,
            //         package.number,
            //         package.internal_id,
            //         stat.status_en as stat,
            //         package.container_id,
            //         package.position_id,
            //         pos.name as position,
            //         package.gross_weight,
            //         cat.name_en as category,
            //         item.price,
            //         cur.name as currency,
            //         item.invoice_doc,
            //         item.invoice_uploaded_date
            //     FROM package 
            //         LEFT JOIN item on item.package_id = package.id
            //         LEFT JOIN currency as cur on item.currency_id = cur.id
            //         LEFT JOIN category as cat on item.category_id = cat.id
            //         LEFT JOIN position as pos on package.position_id = pos.id
            //         LEFT JOIN lb_status as stat on package.last_status_id = stat.id
            //     where item.deleted_by is null 
            //     and package.deleted_by is null 
            //     and package.in_baku != 1
            //     and package.departure_id = '$user_location' 
            //     and package.last_status_id = '$search_status' "
            // ));
            

            $query = Item::LeftJoin('package', 'item.package_id', '=', 'package.id')
            ->leftJoin('currency as cur', 'item.currency_id', '=', 'cur.id')
            ->leftJoin('seller as s', 'package.seller_id', '=', 's.id')
            ->leftJoin('position as pos', 'package.position_id', '=', 'pos.id')
            ->leftJoin('lb_status as status', 'package.last_status_id', '=', 'status.id')
            ->where('package.departure_id', Auth::user()->location())
            ->where('package.last_status_id', 40)
            ->where('package.in_baku', 0)
            ->whereNull('item.deleted_by')
            ->whereNull('package.deleted_by');
            $packages = $query
            ->orderBy('package.id', 'DESC')
            ->select(
                'package.id',
                'package.carrier_status_id',
                'package.number',
                'package.internal_id',
                'status.status_en as status',
                'package.container_id',
                'package.position_id',
                'pos.name as position',
                'package.gross_weight',
                's.name as seller',
                'item.price',
                'cur.name as currency',
                'item.invoice_doc',
                'item.invoice_uploaded_date'
            )
            ->paginate(50);

            if(Auth::user()->role() == 11){
                return view('backend.collector.external_declared_packages')->with([
                    'packages' => $packages,
                ]);
            }

            return view('backend.collector.collector_packages')->with([
                'packages' => $packages,
            ]);
        } catch (\Exception $exception) {
            //dd($exception);
            return view('backend.error');
        }
    }

    public function manager_show_package()
    {
        try {
            $search_arr = array(
                'number' => '',
                'seller' => '',
                'client' => '',
                'status' => '',
                'location' => '',
                'departure' => '',
                'destination' => '',
                'invoice_status' => ''
            );

            //$start = microtime(true);
            $query = Item::leftJoin('package', 'item.package_id', '=', 'package.id')
                ->leftJoin('currency as item_currency', 'item.currency_id', '=', 'item_currency.id')
                ->join('currency as cur', 'package.currency_id', '=', 'cur.id')
                ->leftJoin('seller as s', 'package.seller_id', '=', 's.id')
                ->leftJoin('users as c', 'package.client_id', '=', 'c.id')
                ->join('lb_status as st', 'package.last_status_id', '=', 'st.id')
                ->leftJoin('position as p', 'package.position_id', '=', 'p.id')
                ->leftJoin('locations as l', 'p.location_id', '=', 'l.id')
                ->leftJoin('locations as dep', 'package.departure_id', '=', 'dep.id')
                ->leftJoin('filial', 'package.branch_id', '=', 'filial.id')
                ->whereNull('package.deleted_by');

            if (!empty(Input::get('no')) && Input::get('no') != '' && Input::get('no') != null) {
                $where_no = Input::get('no');
                $query->where('package.id', $where_no);
                $search_arr['no'] = $where_no;
            }

            if (!empty(Input::get('number')) && Input::get('number') != '' && Input::get('number') != null) {
                $where_number = Input::get('number');
                $query->whereRaw('(package.number LIKE "%' . $where_number . '%" or package.internal_id LIKE "%' . $where_number . '%")');
                $search_arr['number'] = $where_number;
            }

            if (!empty(Input::get('client')) && Input::get('client') != '' && Input::get('client') != null) {
                $where_client = Input::get('client');
                $query->where('package.client_id', $where_client);
                $search_arr['client'] = $where_client;
            }

            if (!empty(Input::get('seller')) && Input::get('seller') != '' && Input::get('seller') != null) {
                $where_seller = Input::get('seller');
                $query->where('package.seller_id', $where_seller);
                $search_arr['seller'] = $where_seller;
            }

            if (!empty(Input::get('status')) && Input::get('status') != '' && Input::get('status') != null) {
                $where_status = Input::get('status');
                $query->where('package.last_status_id', $where_status);
                $search_arr['status'] = $where_status;
            }

            if (!empty(Input::get('location')) && Input::get('location') != '' && Input::get('location') != null) {
                $where_location = Input::get('location');
                if ($where_location == 'container') {
                    $query->whereNotNull('package.container_id');
                } else {
                    $query->where('p.location_id', $where_location);
                }
                $search_arr['location'] = $where_location;
            }

            if (!empty(Input::get('departure')) && Input::get('departure') != '' && Input::get('departure') != null) {
                $where_departure = Input::get('departure');
                $query->where('package.departure_id', $where_departure);
                $search_arr['departure'] = $where_departure;
            }

            if (!empty(Input::get('destination')) && Input::get('destination') != '' && Input::get('destination') != null) {
                $where_destination = Input::get('destination');
                $query->where('package.destination_id', $where_destination);
                $search_arr['destination'] = $where_destination;
            }

            if (!empty(Input::get('invoice_status')) && Input::get('invoice_status') != '' && Input::get('invoice_status') != null) {
                $where_invoice_status = Input::get('invoice_status');
                $search_arr['invoice_status'] = $where_invoice_status;

                switch ($where_invoice_status) {
                    case 'no_invoice':
                        {
                            $query->whereNull('item.invoice_doc');
                        }
                        break;
                    case 'not_confirmed':
                        {
                            $query->where('item.invoice_confirmed', 0);
                        }
                        break;
                    case 'confirmed':
                        {
                            $query->where('item.invoice_confirmed', 1);
                        }
                        break;
                }
            }

            //short by start
            $short_by = 'package.created_at';
            $shortType = 'DESC';
            if (!empty(Input::get('shortBy')) && Input::get('shortBy') != '' && Input::get('shortBy') != null) {
                $short_by = Input::get('shortBy');
            }
            if (!empty(Input::get('shortType')) && Input::get('shortType') != '' && Input::get('shortType') != null) {
                $short_type = Input::get('shortType');
                if ($short_type == 2) {
                    $shortType = 'DESC';
                } else {
                    $shortType = 'ASC';
                }
            }

            if( $search_arr['number'] != "" ||  $search_arr['client'] != "" ||  $search_arr['seller'] != "" || $search_arr['status'] != "" || $search_arr['location'] != "" || $search_arr['departure'] != "" || $search_arr['destination'] != "")
            {
                $packages = $query->orderBy($short_by, $shortType)
                    ->select(
                        'package.id',
                        'package.last_status_id',
                        'package.number',
                        'package.height',
                        'package.width',
                        'package.length',
                        'package.gross_weight',
                        'package.unit',
                        'package.total_charge_value',
                        'package.paid',
                        'package.paid_status',
                        'package.carrier_status_id',
                        'package.carrier_registration_number',
                        'package.seller_id',
                        'package.other_seller',
                        'cur.name as currency',
                        's.name as seller',
                        'c.name as client_name',
                        'c.surname as client_surname',
                        'package.client_id',
                        'package.created_at',
                        'st.status_en as status',
                        'st.color as status_color',
                        'p.name as position',
                        'l.name as location',
                        'package.container_id as container',
                        'dep.name as departure',
                        'item.price',
                        'item_currency.name as invoice_currency',
                        'item.invoice_doc',
                        'filial.name as branch'
                    )
                    ->paginate(20);
                
            }
            else
            {
                $packages = $query->orderBy($short_by, $shortType)
                    ->select(
                        'package.id',
                        'package.last_status_id',
                        'package.number',
                        'package.height',
                        'package.width',
                        'package.length',
                        'package.gross_weight',
                        'package.unit',
                        'package.total_charge_value',
                        'package.paid',
                        'package.paid_status',
                        'package.carrier_status_id',
                        'package.carrier_registration_number',
                        'package.seller_id',
                        'package.other_seller',
                        'cur.name as currency',
                        's.name as seller',
                        'c.name as client_name',
                        'c.surname as client_surname',
                        'package.client_id',
                        'package.created_at',
                        'st.status_en as status',
                        'st.color as status_color',
                        'p.name as position',
                        'l.name as location',
                        'package.container_id as container',
                        'dep.name as departure',
                        'item.price',
                        'item_currency.name as invoice_currency',
                        'item.invoice_doc',
                        'filial.name as branch'
                    );
                
            }
            
        
            $sellers = Seller::whereNull('deleted_by')->where('only_collector', 0)->select('id', 'name')->get();
            $statuses = Status::whereNull('deleted_by')->select('id', 'status_en as status')->get();
            $tariff_types = TariffType::whereNull('deleted_by')->select('id', 'name_en as name')->get();
            $locations = Location::whereNull('deleted_by')->select('id', 'name')->get();

            return view('backend.packages', compact(
                'packages',
                'sellers',
                'statuses',
                'locations',
                'tariff_types',
                'search_arr'
            ));
        } catch (\Exception $exception) {
            //dd($exception);
            return view('backend.error');
        }
    }
}

