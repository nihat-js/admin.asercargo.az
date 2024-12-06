<?php

namespace App\Http\Controllers\Api;

use App\BalanceLog;
use App\CashierLog;
use App\CourierAreas;
use App\CourierOrders;
use App\CourierOrderStatus;
use App\CourierPaymentTypes;
use App\CourierRegion;
use App\ExchangeRate;
use App\Option;
use App\Package;
use App\PackageStatus;
use App\PaymentLog;
use App\Receipts;
use App\Status;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CourierController extends Controller
{
    public function get_orders(Request $request)
    {

        try {
            $date = Carbon::now();
            $rates = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
                ->where('to_currency_id', 3) // to AZN
                ->select('rate', 'from_currency_id', 'to_currency_id')
                ->get();

            $has_rate = true;
            if (count($rates) == 0) {
                $has_rate = false;
            }

            $params = false;
            $query = CourierOrders::leftJoin('courier_areas', 'courier_orders.area_id', '=', 'courier_areas.id')
                ->leftJoin('courier_regions', 'courier_orders.region_id', '=', 'courier_regions.id')
                ->leftJoin('courier_metro_stations', 'courier_orders.metro_station_id', '=', 'courier_metro_stations.id')
                ->leftJoin('courier_payment_types', 'courier_orders.courier_payment_type_id', '=', 'courier_payment_types.id')
                ->leftJoin('courier_payment_types as delivery_payment_types', 'courier_orders.delivery_payment_type_id', '=', 'delivery_payment_types.id')
                ->leftJoin('users as client', 'courier_orders.client_id', '=', 'client.id')
                ->leftJoin('users as courier', 'courier_orders.courier_id', '=', 'courier.id')
                ->leftJoin('lb_status as status', 'courier_orders.last_status_id', '=', 'status.id')
                //->whereDate('courier_orders.date', '>=', Carbon::today())
                ->whereRaw('(
                (courier_orders.courier_payment_type_id = 1 and courier_orders.is_paid = 1) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id <> 1) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_amount = 0) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id = 1 and courier_orders.delivery_amount > 0 and courier_orders.is_paid = 1)
                )');

            $where_no = Input::get("no");
            $where_name = Input::get("name");
            $where_surname = Input::get("surname");
            $where_suite = Input::get("suite");
            $where_status = Input::get("status");
            $where_courier = Input::get("courier");
            $where_area = Input::get("areas");
            $where_region = Input::get("regions");
            $where_courier_payment_type = Input::get("courier_payment_type");
            $where_delivery_payment_type = Input::get("delivery_payment_type");
            $where_date = Input::get("date");

            if (isset($where_no) && !empty($where_no) && $where_no != null && $where_no != "null" && $where_no != "undefined") {
                $query->where('courier_orders.id', $where_no);
                $params = true;
            }
            if (isset($where_name) && !empty($where_name) && $where_name != null && $where_name != "null" && $where_name != "undefined") {
                $query->where('client.name', 'like', '%' . $where_name . '%');
                $params = true;
            }
            if (isset($where_surname) && !empty($where_surname) && $where_surname != null && $where_surname != "null" && $where_surname != "undefined") {
                $query->where('client.surname', 'like', '%' . $where_surname . '%');
                $params = true;
            }
            if (isset($where_suite) && !empty($where_suite) && $where_suite != null && $where_suite != "null" && $where_suite != "undefined") {
                $query->where('courier_orders.client_id', $where_suite);
                $params = true;
            }
            if (isset($where_status) && !empty($where_status) && $where_status != null && $where_status != "null" && $where_status != "undefined") {
                $query->where('courier_orders.last_status_id', $where_status);
                $params = true;
            }
            if (isset($where_courier) && !empty($where_courier) && $where_courier != null && $where_courier != "null" && $where_courier != "undefined") {
                $query->where('courier_orders.courier_id', $where_courier);
                $params = true;
            }
            if (isset($where_area) && !empty($where_area) && $where_area != null && $where_area != "null" && $where_area != "undefined") {
                $query->where('courier_orders.area_id', $where_area);
                $params = true;
            }

            if (isset($where_region) && !empty($where_region) && $where_region != null && $where_region != "null" && $where_region != "undefined") {
                $query->where('courier_orders.region_id', $where_region);
                $params = true;
            }

            if (isset($where_courier_payment_type) && !empty($where_courier_payment_type) && $where_courier_payment_type != null && $where_courier_payment_type != "null" && $where_courier_payment_type != "undefined") {
                $query->where('courier_orders.courier_payment_type_id', $where_courier_payment_type);
                $params = true;
            }
            if (isset($where_delivery_payment_type) && !empty($where_delivery_payment_type) && $where_delivery_payment_type != null && $where_delivery_payment_type != "null" && $where_delivery_payment_type != "undefined") {
                $query->where('courier_orders.delivery_payment_type_id', $where_delivery_payment_type);
                $params = true;
            }
            if (isset($where_date) && !empty($where_date) && $where_date != null && $where_date != "null" && $where_date != "undefined") {
                $query->whereDate('courier_orders.date', $where_date);
                $params = true;
            }


            if ($params == true){
                $orders = $query->select(
                    'courier_orders.id',
                    'courier_orders.urgent',
                    'courier_orders.client_id as suite',
                    'client.passport_number',
                    'client.name as client_name',
                    'client.surname as client_surname',
                    'courier_orders.phone',
                    'courier_areas.name_en as area',
                    'courier_regions.name_en as region',
                    'courier_metro_stations.name_en as metro_station',
                    'courier_orders.address',
                    'courier_orders.date',
                    'courier_payment_types.name_en as courier_payment_type',
                    'delivery_payment_types.name_en as delivery_payment_type',
                    'courier_orders.packages',
                    'courier_orders.amount as shipping_amount',
                    'courier_orders.delivery_amount as delivery_amount',
                    'courier_orders.total_amount as total_amount',
                    'courier_orders.has_courier',
                    'courier_orders.courier_id',
                    'courier.name as courier_name',
                    'courier.surname as courier_surname',
                    'status.status_en as status',
                    'courier_orders.created_at',
                    'courier_orders.post_zip',
                )
                    ->orderBy('courier_orders.date')
                    ->orderBy('courier_orders.urgent', 'desc')
                    ->orderBy('courier_orders.id')
                    ->paginate(30);

                foreach ($orders as $order) {
                    $packages_str = $order->packages;
                    $packages_arr = explode(',', $packages_str);

                    $packages = Package::leftJoin('users as client', 'package.client_id', '=', 'client.id')
                        ->whereIn('package.id', $packages_arr)
                        ->select(
                            'package.id',
                            'package.number',
                            'package.internal_id',
                            'package.total_charge_value as amount',
                            'package.currency_id',
                            'package.paid_status',
                            'client.name as client_name',
                            'client.surname as client_surname'
                        )
                        ->get();

                    foreach ($packages as $package) {
                        $currency_id = $package->currency_id;

                        if ($has_rate) {
                            $rate_to_azn = $this->calculate_exchange_rate($rates, $currency_id, 3);
                        } else {
                            $rate_to_azn = 1;
                        }
                        $amount_azn = $package->amount * $rate_to_azn;
                        $amount_azn = sprintf('%0.2f', $amount_azn);

                        $package->amount = $amount_azn;

                        if ($package->paid_status == 1) {
                            $package->paid_status = 'Paid';
                        } else {
                            $package->paid_status = 'Not paid';
                        }
                    }

                    //$order->update = $order->date;
                    // dd($update);
                    $order->packages_object = $packages;
                }

                return response()->json($orders);
            }else{

                return response()->json([], Response::HTTP_NOT_FOUND);
            }


        } catch (\Exception $exception) {
            //dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }


    }

    public function get_packages(Request $request){
        $validator = Validator::make($request->all(), [
            'packages' => ['required', 'string', 'max:1000'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => __('courier.incomplete_information')]);
        }

        try {

            $packages_list = $request->packages;
            $packages_list_arr = explode(',', $packages_list);

            $packages = Package::whereNull('deleted_by')
                ->whereNull('package.deleted_by')
                ->whereIn('id', $packages_list_arr)
                ->select(
                    'id',
                    'package.total_charge_value as amount',
                    'package.paid',
                    'package.currency_id',
                    'paid_status',
                    'internal_id',
                    'height',
                    'width',
                    'gross_weight',
                    'total_charge_value'
                )
                ->get();

            if (count($packages) == 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => __('courier.packages_not_conditions')]);
            }

            return response()->json($packages);

        }catch (\Exception $exception){
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Error']);
        }
    }

    public function GetArea(){

        $areas = CourierAreas::whereNull('deleted_at')->get();

        return response()->json($areas);
    }

    public function GetRegion(){

        $region = CourierRegion::whereNull('deleted_at')->get();

        return response()->json($region);
    }

    public function GetStatus(){

        $query = "
            SELECT DISTINCT id, status_az
            FROM lb_status
            WHERE deleted_at IS NULL
            AND for_courier = 1
            OR for_partner = 1
        ";

        $status = \DB::select(\DB::raw($query));

        return response()->json($status);
    }

    public function get_courier_user()
    {
        try {
            $couriers = User::where('role_id', 8)->whereNull('deleted_by')->select('id', 'name', 'surname')->get();

            return response()->json($couriers);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Error']);
        }
    }

    public function update_status(Request $request, $order)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'integer']
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => __('courier.incomplete_information')]);
        }
        try {

            $this->set_to_paid_and_delivered($request, $order);
            /*$order = CourierOrders::findOrFail($order);
            $status_id = Status::findOrFail($request->status);


            $packages_list = $order;
            $packages_list_arr = explode(',', $packages_list->packages);


            $packages = Package::whereNull('deleted_by')
                ->whereIn('id', $packages_list_arr)
                ->select('id')
                ->get();


            if (count($packages) == 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => __('courier.packages_not_conditions')]);
            }

            foreach ($packages as $package){
                if ($status_id->id == Status::delivered || $status_id->id == Status::delivered_by_azeripost || $status_id->id == Status::delivered_by_courier){
                    $package->update([
                        'last_status_id' => $status_id->id,
                        'last_status_date' => Carbon::now(),
                        'delivered_at' => Carbon::now(),
                        'delivered_by' => $order->courier_id
                    ]);
                }else{
                    $package->update([
                        'last_status_id' => $status_id->id,
                        'last_status_date' => Carbon::now()
                    ]);
                }



                PackageStatus::create([
                    'package_id' => $package->id,
                    'status_id' => $status_id->id,
                    'created_by' => 1
                ]);
            }

            CourierOrderStatus::create([
                'order_id' => $order->id,
                'status_id' => $status_id->id,
                'created_by' => 1
            ]);*/

            return response(['case' => 'Success', 'title' => 'Success!', 'content' => 'ok']);

        }catch (\Exception $exception){
           // dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Status or Courier order not found']);
        }
    }

    public function set_courier(Request $request, $order)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'integer'],
            'courier' => ['required', 'integer']
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => __('courier.incomplete_information')]);
        }
        try {
            $order = CourierOrders::findOrFail($request->order);
            $status_id = Status::findOrFail($request->status);
            $courier = User::where('id', $request->courier)->where('role_id', 8)->whereNull('deleted_by')->select('id', 'name', 'surname')->first();


            if ($courier == null){
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Courier not found']);
            }

            $packages_list = $order;
            $packages_list_arr = explode(',', $packages_list->packages);


            $packages = Package::whereNull('deleted_by')
                ->whereIn('id', $packages_list_arr)
                ->select('id')
                ->get();


            if (count($packages) == 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => __('courier.packages_not_conditions')]);
            }

            foreach ($packages as $package){
                $package->update([
                    'last_status_id' => $status_id->id,
                    'last_status_date' => Carbon::now(),
                    'issued_to_courier_date' => Carbon::now(),
                ]);

                PackageStatus::create([
                    'package_id' => $package->id,
                    'status_id' => $status_id->id,
                    'created_by' => 1
                ]);
            }


            $order->update([
                'courier_id' => $courier->id
            ]);

            CourierOrderStatus::create([
                'order_id' => $order->id,
                'status_id' => $status_id->id,
                'created_by' => 1
            ]);

            return response(['case' => 'Success', 'title' => 'Success!', 'content' => 'ok']);

        }catch (\Exception $exception){
            //dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Status or Courier order not found']);
        }

    }


    // private functions
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

    private function set_to_paid_and_delivered($request, $order_id)
    {

        try {
            //$receipt = $request->receipt;
            //$date = $request->date;

            $courier_order_id = $order_id;
            $status_id = Status::findOrFail($request->status);

            $order = CourierOrders::where('id', $courier_order_id)
                ->whereNull('courier_orders.deleted_by')
                ->whereNull('courier_orders.canceled_by')
                ->whereNull('courier_orders.delivered_at')
                ->first();
            //dd($order);
            if (!$order) {
                return response(['case' => 'warning', 'title' => 'Courier order not found!', 'content' => 'Courier order not found! Order canceled or order already delivered!']);
            }

            $client_id = $order->client_id;
            $packages_str = $order->packages;
            $packages_arr = explode(',', $packages_str);
            $total_amount_azn = 0;
            $total_amount_for_response = 0;

            $amount_for_cashier_log_cash_azn_courier = 0;
            $amount_for_cashier_log_cash_azn_shipping = 0;
            $amount_for_cashier_log_pos_azn_courier = 0;
            $amount_for_cashier_log_pos_azn_shipping = 0;

            if ($status_id->id == Status::delivered || $status_id->id == Status::delivered_by_azeripost || $status_id->id == Status::delivered_by_courier){
              // dd('status delivered geldi');
                $today = Carbon::now();
                $rates = ExchangeRate::whereDate('from_date', '<=', $today)->whereDate('to_date', '>=', $today)
                    ->select('rate', 'from_currency_id', 'to_currency_id')
                    ->get();



                if (count($rates) == 0) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rates not found!']);
                }



                $rate_azn_to_usd = $this->calculate_exchange_rate($rates, 3, 1);

                if ($order->is_paid == 0 && $order->courier_payment_type_id != 1) {
                    // pay to order
                    CourierOrders::where('id', $courier_order_id)
                        ->update([
                            'paid' => $order->amount,
                            'is_paid' => 1
                        ]);

                    switch ($order->courier_payment_type_id) {
                        case 3:
                            {
                                // POS
                                $courier_payment_type_for_payment_log = 2;

                                $amount_for_cashier_log_pos_azn_courier += $order->amount;
                            }
                            break;
                        default:
                        {
                            // cash (2)
                            $courier_payment_type_for_payment_log = 1;

                            $total_amount_for_response += $order->amount;
                            $amount_for_cashier_log_cash_azn_courier += $order->amount;
                        }
                    }

                    PaymentLog::create([
                        'payment' => $order->amount,
                        'currency_id' => 3, // azn
                        'client_id' => $client_id,
                        'package_id' => $courier_order_id,
                        'is_courier_order' => 1, // 1 - yes, 2- no
                        'type' => $courier_payment_type_for_payment_log,
                        'created_by' => 1
                    ]);

                    $total_amount_azn += $order->amount;
                }


                switch ($order->delivery_payment_type_id) {
                    case 3:
                        {
                            // POS
                            $delivery_payment_type_for_payment_log = 2;
                        }
                        break;
                    default:
                    {
                        // cash (2)
                        $delivery_payment_type_for_payment_log = 1;
                    }
                }

                $packages = Package::whereIn('id', $packages_arr)
                    //->whereNotNull('courier_by')
                    ->whereNull('delivered_by')
                    ->whereNull('deleted_by')
                    ->whereNotNull('issued_to_courier_date')
                    ->select('id', 'total_charge_value as amount', 'paid', 'currency_id', 'paid_status')
                    ->get();
                //dd($packages);
                foreach ($packages as $package) {
                    if ($order->delivery_payment_type_id != 1 && $package->paid_status == 0) {
                        $package_amount = $package->amount - $package->paid;

                        PaymentLog::create([
                            'payment' => $package_amount,
                            'currency_id' => $package->currency_id,
                            'client_id' => $client_id,
                            'package_id' => $package->id,
                            'type' => $delivery_payment_type_for_payment_log,
                            'created_by' => 1
                        ]);

                        $currency_id = $package->currency_id;
                        $rate_to_azn = $this->calculate_exchange_rate($rates, $currency_id, 3);
                        $package_amount_azn = $package_amount * $rate_to_azn;

                        $total_amount_azn += $package_amount_azn;

                        if ($order->delivery_payment_type_id == 2) {
                            // cash
                            $total_amount_for_response += $package_amount_azn;
                            $amount_for_cashier_log_cash_azn_shipping += $package_amount_azn;
                        } else {
                            // 3 pos
                            $amount_for_cashier_log_pos_azn_shipping += $package_amount_azn;
                        }
                    }

                    PackageStatus::create([
                        'package_id' => $package->id,
                        'status_id' => $status_id->id,
                        'created_by' => 1
                    ]);
                }

                Package::whereIn('id', $packages_arr)
                    //->whereNotNull('courier_by')
                    ->whereNull('delivered_by')
                    ->whereNull('deleted_by')
                    ->whereNotNull('issued_to_courier_date')
                    ->update([
                        'paid' => DB::raw("`total_charge_value`"),
                        'paid_status' => 1,
                        'payment_type_id' => $order->delivery_payment_type_id,
                        'delivered_by' => $order->courier_id,
                        'delivered_at' => $today,
                        'last_status_id' => $status_id->id,
                        'last_status_date' => Carbon::now(),
                    ]);

                $total_amount_usd = $total_amount_azn * $rate_azn_to_usd;
                $total_amount_azn = sprintf('%0.2f', $total_amount_azn);

                if ($total_amount_azn > 0) {
                    $payment_code = Str::random(20);
                    BalanceLog::create([
                        'payment_code' => $payment_code,
                        'amount' => $total_amount_usd,
                        'amount_azn' => $total_amount_azn,
                        'client_id' => $client_id,
                        'status' => 'in',
                        'type' => 'courier'
                    ]);

                    $payment_code = Str::random(20);
                    BalanceLog::create([
                        'payment_code' => $payment_code,
                        'amount' => $total_amount_usd,
                        'amount_azn' => $total_amount_azn,
                        'client_id' => $client_id,
                        'status' => 'out',
                        'type' => 'courier'
                    ]);
                }

                CourierOrders::where('id', $order->id)
                    ->update([
                        'canceled_by' => null,
                        'canceled_at' => null,
                        'delivered_at' => Carbon::now()
                    ]);

                CourierOrderStatus::create([
                    'order_id' => $order->id,
                    'status_id' => $status_id->id,
                    'created_by' => 1
                ]);

                $client = User::where('id', $client_id)->select('balance')->first();
                //dd($client);
                if ($amount_for_cashier_log_cash_azn_courier > 0) {
                    $amount_for_cashier_log_cash_usd_courier = $amount_for_cashier_log_cash_azn_courier * $rate_azn_to_usd;
                    $amount_for_cashier_log_cash_usd_courier = sprintf('%0.2f', $amount_for_cashier_log_cash_usd_courier);

                    CashierLog::create([
                        'payment_azn' => $amount_for_cashier_log_cash_azn_courier,
                        'payment_usd' => $amount_for_cashier_log_cash_usd_courier,
                        'added_to_balance' => 0, //azn
                        'old_balance' => $client->balance, //usd
                        'new_balance' => $client->balance, //usd
                        'client_id' => $client_id,
                        'receipt' => 'system',
                        'type' => 'Cash (Courier Delivery)',
                        'created_by' => 1
                    ]);
                }

                if ($amount_for_cashier_log_cash_azn_shipping > 0) {
                    $amount_for_cashier_log_cash_usd_shipping = $amount_for_cashier_log_cash_azn_shipping * $rate_azn_to_usd;
                    $amount_for_cashier_log_cash_usd_shipping = sprintf('%0.2f', $amount_for_cashier_log_cash_usd_shipping);

                    CashierLog::create([
                        'payment_azn' => $amount_for_cashier_log_cash_azn_shipping,
                        'payment_usd' => $amount_for_cashier_log_cash_usd_shipping,
                        'added_to_balance' => 0, //azn
                        'old_balance' => $client->balance, //usd
                        'new_balance' => $client->balance, //usd
                        'client_id' => $client_id,
                        'receipt' => 'system',
                        'type' => 'Cash (Courier Shipping)',
                        'created_by' => 1
                    ]);
                }

                $success_message = 'Paid: ' . $total_amount_for_response . ' AZN and Delivered!';
            }
            else{

                Package::whereIn('id', $packages_arr)
                    ->whereNull('delivered_by')
                    ->whereNull('deleted_by')
                    ->whereNotNull('issued_to_courier_date')
                    ->update([
                        'last_status_id' => $status_id->id,
                        'last_status_date' => now(),
                    ]);

                $packageStatusRecords = [];
                foreach ($packages_arr as $packageId) {
                    $packageStatusRecords[] = [
                        'package_id' => $packageId,
                        'status_id' => $status_id->id,
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                PackageStatus::insert($packageStatusRecords);

                CourierOrderStatus::create([
                    'order_id' => $order->id,
                    'status_id' => $status_id->id,
                    'created_by' => 1
                ]);

            }


            return response(['case' => 'success', 'content' => $success_message, 'paid_amount' => $total_amount_for_response, 'id' => $courier_order_id]);
        }
        catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }
}
