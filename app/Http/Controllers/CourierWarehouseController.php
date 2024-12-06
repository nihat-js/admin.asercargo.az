<?php

namespace App\Http\Controllers;

use App\CourierAreas;
use App\CourierOrders;
use App\CourierOrderStatus;
use App\CourierPaymentTypes;
use App\CourierRegion;
use App\ExchangeRate;
use App\Exports\CourierOrdersExport;
use App\Option;
use App\Package;
use App\PackageStatus;
use App\Receipts;
use App\Status;
use App\User;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class CourierWarehouseController extends HomeController
{
    public function get_courier_page()
    {
        try {
            $statuses = Status::where('for_courier', 1)->select('id', 'status_en as name')->get();
            $couriers = User::where('role_id', 8)->whereNull('deleted_by')->select('id', 'name', 'surname')->get();
            $printers = Option::whereNull('deleted_by')->select('title', 'device2 as ip')->get();
            $areas = CourierAreas::select('id', 'name_en as name')->get();
            $regions = CourierRegion::select('id', 'name_en as name')->get();
            $payment_types = CourierPaymentTypes::select('id', 'name_en as name')->get();

            return view('backend.warehouse.courier', compact('statuses', 'couriers', 'printers', 'areas', 'payment_types', 'regions'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function get_courier_page_for_courier_user()
    {
        try {
            $statuses = Status::where('for_courier', 1)->select('id', 'status_en as name')->get();
            $couriers = User::where('role_id', 8)->whereNull('deleted_by')->select('id', 'name', 'surname')->get();
            $printers = Option::whereNull('deleted_by')->select('title', 'device2 as ip')->get();
            $areas = CourierAreas::select('id', 'name_en as name')->get();
            $payment_types = CourierPaymentTypes::select('id', 'name_en as name')->get();

            return view('backend.courier_user.courier', compact('statuses', 'couriers', 'printers', 'areas', 'payment_types'));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function show_courier_orders()
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
            $where_old_orders = Input::get("old_orders");
            // dd($where_region, $where_area);
            if (isset($where_no) && !empty($where_no) && $where_no != null && $where_no != "null" && $where_no != "undefined") {
                $query->where('courier_orders.id', $where_no);
            }
            if (isset($where_name) && !empty($where_name) && $where_name != null && $where_name != "null" && $where_name != "undefined") {
                $query->where('client.name', 'like', '%' . $where_name . '%');
            }
            if (isset($where_surname) && !empty($where_surname) && $where_surname != null && $where_surname != "null" && $where_surname != "undefined") {
                $query->where('client.surname', 'like', '%' . $where_surname . '%');
            }
            if (isset($where_suite) && !empty($where_suite) && $where_suite != null && $where_suite != "null" && $where_suite != "undefined") {
                $query->where('courier_orders.client_id', $where_suite);
            }
            if (isset($where_status) && !empty($where_status) && $where_status != null && $where_status != "null" && $where_status != "undefined") {
                $query->where('courier_orders.last_status_id', $where_status);
            }
            if (isset($where_courier) && !empty($where_courier) && $where_courier != null && $where_courier != "null" && $where_courier != "undefined") {
                $query->where('courier_orders.courier_id', $where_courier);
            }
            if (isset($where_area) && !empty($where_area) && $where_area != null && $where_area != "null" && $where_area != "undefined") {
                $query->where('courier_orders.area_id', $where_area);
            }
            
            if (isset($where_region) && !empty($where_region) && $where_region != null && $where_region != "null" && $where_region != "undefined") {
                $query->where('courier_orders.region_id', $where_region);
            }

            if (isset($where_courier_payment_type) && !empty($where_courier_payment_type) && $where_courier_payment_type != null && $where_courier_payment_type != "null" && $where_courier_payment_type != "undefined") {
                $query->where('courier_orders.courier_payment_type_id', $where_courier_payment_type);
            }
            if (isset($where_delivery_payment_type) && !empty($where_delivery_payment_type) && $where_delivery_payment_type != null && $where_delivery_payment_type != "null" && $where_delivery_payment_type != "undefined") {
                $query->where('courier_orders.delivery_payment_type_id', $where_delivery_payment_type);
            }
            if (isset($where_date) && !empty($where_date) && $where_date != null && $where_date != "null" && $where_date != "undefined") {
                $query->whereDate('courier_orders.date', $where_date);
            }
            if (!isset($where_old_orders) || $where_old_orders == null || $where_old_orders == "null" || $where_old_orders == "undefined" || $where_old_orders == 0) {
                $query->whereDate('courier_orders.date', '>=', $date);
            }

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
                'courier_orders.azerpost_track',
                'courier_orders.order_weight',
                'courier_payment_types.name_en as courier_payment_type',
                'delivery_payment_types.name_en as delivery_payment_type',
                'courier_orders.packages',
                'courier_orders.amount as delivery_amount',
                'courier_orders.delivery_amount as shipping_amount',
                'courier_orders.total_amount as summary_amount',
                'courier_orders.has_courier',
                'courier_orders.courier_id',
                'courier.name as courier_name',
                'courier.surname as courier_surname',
                'status.status_en as status',
                'courier_orders.created_at',
                'courier_orders.post_zip',
                'courier_orders.azerpost_track',
                'courier_orders.is_send_azerpost',
                'courier_orders.is_set_azerpost',
                'courier_orders.is_error',
            )
                //                ->orderByRaw('DATE(courier_orders.date)=DATE(NOW()) DESC,
                //                            DATE(courier_orders.date)<DATE(NOW()) DESC,
                //                            DATE(courier_orders.date)>DATE(NOW()) ASC')
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

                $order->update = $order->date;
                // dd($update);
                $order->packages_object = $packages;
            }


            return response(['case' => 'success', 'orders' => $orders]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function choose_courier_for_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => ['required', 'integer'],
            'courier_id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $order_id = $request->order_id;
            $courier_id = $request->courier_id;

            $order = CourierOrders::where('id', $order_id)
                ->whereNull('deleted_by')
                // ->whereNull('delivered_at')
                ->select('date', 'packages', 'post_zip', 'delivery_longitude', 'delivery_latitude')
                ->first();
     
            // if (!$order) {
            //     return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Order not found or order delivered!']);
            // }

            if ($courier_id == 1907) {
                // cancel
                CourierOrders::where('id', $order_id)
                    // ->whereNull('delivered_at')
                    ->update([
                        'canceled_by' => Auth::id(),
                        'canceled_at' => Carbon::now(),
                        'courier_id' => null,
                        'collected_by' => null,
                        'collected_at' => null
                    ]);

                $packages_str = $order->packages;
                $packages_arr = explode(',', $packages_str);

                Package::whereIn('id', $packages_arr)
                    ->whereNull('delivered_by')
                    ->whereNull('deleted_by')
                    ->update([
                        'issued_to_courier_date' => null,
                        'courier_order_id' => null,
                        'has_courier' => 0,
                        'has_courier_by' => null,
                        //'has_courier_at' => null,
                        'courier_by' => null,
                        //'courier_at' => null,
                    ]);

                $status_id = 12; // canceled

                 // ------------------ 189 deleted method start--------------
                    
                 $internalOrderId = 'CLBR' .$order_id;
            
                 if($order->post_zip == null && $order->delivery_longitude != null && $order->delivery_latitude != null){
                     
                     $curl = curl_init();
    
                     curl_setopt_array($curl, array(
                     CURLOPT_URL => 'http://94.130.27.14:8062/services/189couriermsorders/api/partner/orders/internalorderid/'.$internalOrderId,
                     CURLOPT_RETURNTRANSFER => true,
                     CURLOPT_ENCODING => '',
                     CURLOPT_MAXREDIRS => 10,
                     CURLOPT_TIMEOUT => 0,
                     CURLOPT_FOLLOWLOCATION => true,
                     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                     CURLOPT_CUSTOMREQUEST => 'DELETE',
                     CURLOPT_HTTPHEADER => array(
                            'X-Username: colibriexpress',
                            'X-Access-token: d75d75e57i75e75ed'
                        ),
                     ));
     
                     $response = curl_exec($curl);
     
                     curl_close($curl);
                    //  dd($response);
                 }

 
 
                // ------------------ 189 deleted method end--------------

            } else {
                // order collected
                $date = Carbon::now();

                CourierOrders::where('id', $order_id)
                    ->whereNull('delivered_at')
                    ->update([
                        'collected_by' => Auth::id(),
                        'collected_at' => $date,
                        'courier_id' => $courier_id,
                        'canceled_by' => null,
                        'canceled_at' => null
                    ]);

                $packages_str = $order->packages;
                $packages_arr = explode(',', $packages_str);

                Package::whereIn('id', $packages_arr)
                    ->whereNull('delivered_by')
                    ->whereNull('deleted_by')
                    ->update(['issued_to_courier_date' => $date]);

                $status_id = 30; // courier
            }

            CourierOrderStatus::create([
                'order_id' => $order_id,
                'status_id' => $status_id,
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            
            dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function delivered_to_the_courier(Request $request) {
        $validator = Validator::make($request->all(), [
            'order_id' => ['required', 'integer'],
            'has_courier' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $order_id = $request->order_id;
            $has_courier = $request->has_courier;

            CourierOrders::where('id', $order_id)
                ->update([
                    'has_courier' => $has_courier,
                    'has_courier_by' => Auth::id(),
                    'has_courier_at' => Carbon::now()
                ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function print_courier_receipt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
            'ip' => ['required', 'string', 'max:30'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
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

            $order = CourierOrders::leftJoin('courier_payment_types', 'courier_orders.courier_payment_type_id', '=', 'courier_payment_types.id')
                ->leftJoin('courier_payment_types as delivery_payment_types', 'courier_orders.delivery_payment_type_id', '=', 'delivery_payment_types.id')
                ->leftJoin('users as client', 'courier_orders.client_id', '=', 'client.id')
                ->leftJoin('users as courier', 'courier_orders.courier_id', '=', 'courier.id')
                ->where('courier_orders.id', $request->id)
                ->whereNotNull('courier_orders.courier_id')
                ->whereRaw('(
                (courier_orders.courier_payment_type_id = 1 and courier_orders.is_paid = 1) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id <> 1) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_amount = 0) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id = 1 and courier_orders.delivery_amount > 0 and courier_orders.is_paid = 1)
                )')
                ->select(
                    'courier_orders.id',
                    'courier_orders.print_date',
                    'courier_orders.urgent',
                    'courier_orders.client_id as suite',
                    'client.name as client_name',
                    'client.surname as client_surname',
                    'courier_orders.courier_payment_type_id',
                    'courier_payment_types.name_az as courier_payment_type',
                    'courier_orders.delivery_payment_type_id',
                    'delivery_payment_types.name_az as shipping_payment_type',
                    'courier_orders.courier_payment_type_id',
                    'courier_orders.is_paid',
                    'courier_orders.packages',
                    'courier_orders.amount as courier_amount',
                    'courier_orders.delivery_amount',
                    'courier_orders.total_amount',
                    'courier_orders.courier_id',
                    'courier.username as courier',
                    )
                ->first();

            if (!$order) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Order not found!']);
            }

            if ($order->print_date != null) {
                $print_date = Carbon::parse($order->print_date);
                $now = Carbon::parse(Carbon::now()->toTimeString());

                $diff_time = $print_date->diffInSeconds($now, false);

                if ($diff_time <= 5) {
                    $second = 6 - $diff_time;
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'You have printed this order before, please wait ' . $second . ' seconds before reprinting.']);
                }
            }

            $total_cash_amount_azn = 0;

            if ($order->is_paid == 0) {
                $courier_amount_azn = $order->courier_amount;
                $shipping_amount_azn = $order->delivery_amount;
            } else {
                $courier_amount_azn = 0;
                $shipping_amount_azn = 0;
            }

            if ($order->courier_payment_type_id == 2) {
                $total_cash_amount_azn += $courier_amount_azn;
            }

            if ($order->delivery_payment_type_id == 2) {
                $total_cash_amount_azn += $shipping_amount_azn;
            }
    
            if ($order->courier_payment_type_id == 2 && $order->is_paid == 1) {
                $courier_amount_azn = $order->courier_amount;
                $total_cash_amount_azn += $courier_amount_azn;
            }
    
            if ($order->delivery_payment_type_id == 2 && $order->is_paid == 1) {
                $shipping_amount_azn = $order->delivery_amount;
                $total_cash_amount_azn += $order->delivery_amount;
            }

            Receipts::where(['courier_order_id' => $request->id])
                ->whereNull('deleted_by')
                ->update([
                    'deleted_by' => Auth::id(),
                    'deleted_at' => $date
                ]);

            $time = microtime();
            $receipt = 'RC' . substr($time, -5) . rand(0, 9);

            while(Receipts::where('receipt', $receipt)->select('id')->first()) {
                $receipt = 'RC' . substr($time, -5) . rand(0, 9);
            }

            Receipts::create([
                'receipt' => $receipt,
                'courier_order_id' => $request->id,
                'created_by' => Auth::id()
            ]);

            $order_arr = array();
            $order_arr['ip'] = $request->ip;
            $order_arr['receipt'] = $receipt;
            $order_arr['date'] = Carbon::now()->toDateTimeString();
            $order_arr['suite'] = $order->suite;
            $order_arr['client'] = $order->client_name . ' ' . $order->client_surname;
            $order_arr['payment_type'] = '';
            $order_arr['courier'] = $order->courier;
            $order_arr['warehouseman'] = Auth::user()->username();
            $order_arr['cashier'] = 'ceyhuna.rahimova';
            $order_arr['delivery_amount'] = $courier_amount_azn;
            $order_arr['shipping_amount'] = $shipping_amount_azn;
            $order_arr['paid_amount'] = $total_cash_amount_azn;
            $order_arr['courier_payment_type_id'] = $order->courier_payment_type_id;
            $order_arr['courier_payment_type'] = $order->courier_payment_type;
            $order_arr['shipping_payment_type_id'] = $order->delivery_payment_type_id;
            $order_arr['shipping_payment_type'] = $order->shipping_payment_type;

            $packages_str = $order->packages;
            $packages_arr = explode(',', $packages_str);

            $packages = Package::leftJoin('position as p', 'package.position_id', '=', 'p.id')
                ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
                ->leftJoin('courier_payment_types', 'package.payment_type_id', '=', 'courier_payment_types.id')
                ->whereIn('package.id', $packages_arr)
                ->whereNull('package.delivered_by')
                ->whereNull('package.deleted_by')
                ->whereNotNull('package.issued_to_courier_date')
                ->select('package.id', 'package.number', 'p.name as position', 'package.client_id as suite', 'client.name as client_name', 'client.surname as client_surname', 'package.internal_id', 'package.total_charge_value as amount', 'package.paid', 'package.paid_status', 'package.currency_id', 'courier_payment_types.name_az as payment_type',)
                ->get();

            $packages_id_arr = array();
            $packages_response_arr = array();
            //$total_amount_azn = 0;
            //$total_paid_azn = 0;
            foreach ($packages as $package) {
                $package_arr = array();

                $currency_id = $package->currency_id;

                if ($has_rate) {
                    $rate_to_azn = $this->calculate_exchange_rate($rates, $currency_id, 3);
                } else {
                    $rate_to_azn = 1;
                }

                $pay = $package->amount - $package->paid;
                $pay_azn = $pay * $rate_to_azn;
                $pay_azn = sprintf('%0.2f', $pay_azn);

                //$total_paid_azn += $pay_azn;
                //$total_amount_azn += $amount_azn;

                $package_arr['internal_id'] = $package->internal_id;
                $package_arr['suite'] = $package->suite;
                $package_arr['client'] = $package->client_name . ' ' . $package->client_surname;
                $package_arr['position'] = $package->position;
                //$package_arr['amount'] = $amount_azn;
                if ($pay_azn > 0) {
                    $pay_str = $pay_azn;
                } else {
                    $pay_str = '';
                }
                $package_arr['pay'] = $pay_str;
                if ($package->payment_type == null) {
                    $package_arr['payment_type'] = $order->shipping_payment_type;
                } else {
                    $package_arr['payment_type'] = $package->payment_type;
                }

                array_push($packages_response_arr, $package_arr);

                array_push($packages_id_arr, $package->id);
            }

            $response = array();
            $response['order'] = $order_arr;
            $response['packages'] = $packages_response_arr;

            Package::whereIn('id', $packages_id_arr)->update(['payment_receipt' => $receipt, 'payment_receipt_date' => $date]);

            CourierOrders::where('id', $request->id)->update(['print_date' => Carbon::now()]);

            return response(['case' => 'success', 'response' => $response]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function export_courier_orders()
    {
        try {
            set_time_limit(360);

            $export_type = Input::get("type");
            $no = Input::get("no");
            $name = Input::get("name");
            $surname = Input::get("surname");
            $suite = Input::get("suite");
            $status = Input::get("status");
            $courier = Input::get("courier");
            $area = Input::get("areas");
            $region = Input::get("regions");
            $courier_payment_type = Input::get("courier_payment_type");
            $delivery_payment_type = Input::get("delivery_payment_type");
            $date = Input::get("date");
            // dd($export_type,$date, $region, $courier);
            if ($export_type == 2) {
                // excel
                return Excel::download(new CourierOrdersExport($no, $name, $surname, $suite, $status, $courier, $area, $region, $courier_payment_type, $delivery_payment_type, $date), 'courier.xlsx');
            }

            $query = CourierOrders::leftJoin('courier_metro_stations', 'courier_orders.metro_station_id', '=', 'courier_metro_stations.id')
                ->leftJoin('courier_areas', 'courier_orders.area_id', '=', 'courier_areas.id')
                ->leftJoin('courier_regions', 'courier_orders.region_id', '=', 'courier_regions.id')
                ->leftJoin('courier_payment_types', 'courier_orders.courier_payment_type_id', '=', 'courier_payment_types.id')
                ->leftJoin('courier_payment_types as delivery_payment_types', 'courier_orders.delivery_payment_type_id', '=', 'delivery_payment_types.id')
                ->leftJoin('users as client', 'courier_orders.client_id', '=', 'client.id')
                ->leftJoin('users as courier', 'courier_orders.courier_id', '=', 'courier.id')
                ->whereNotNull('courier_orders.courier_id')
                ->whereNull('courier_orders.delivered_at')
                ->whereRaw('(
                (courier_orders.courier_payment_type_id = 1 and courier_orders.is_paid = 1) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id <> 1) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_amount = 0) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id = 1 and courier_orders.delivery_amount > 0 and courier_orders.is_paid = 1)
                )');

            if (isset($no) && !empty($no) && $no != null && $no != "null" && $no != "undefined") {
                $query->where('courier_orders.id', $no);
            }
            if (isset($name) && !empty($name) && $name != null && $name != "null" && $name != "undefined") {
                $query->where('client.name', 'like', '%' . $name . '%');
            }
            if (isset($surname) && !empty($surname) && $surname != null && $surname != "null" && $surname != "undefined") {
                $query->where('client.surname', 'like', '%' . $surname . '%');
            }
            if (isset($suite) && !empty($suite) && $suite != null && $suite != "null" && $suite != "undefined") {
                $query->where('courier_orders.client_id', $suite);
            }
            if (isset($status) && !empty($status) && $status != null && $status != "null" && $status != "undefined") {
                $query->where('courier_orders.last_status_id', $status);
            }
            if (isset($courier) && !empty($courier) && $courier != null && $courier != "null" && $courier != "undefined") {
                $query->where('courier_orders.courier_id', $courier);
            }
            if (isset($area) && !empty($area) && $area != null && $area != "null" && $area != "undefined") {
                $query->where('courier_orders.area_id', $area);
            }

            if (isset($region) && !empty($region) && $region != null && $region != "null" && $region != "undefined") {
                $query->where('courier_orders.region_id', $region);
            }
            if (isset($courier_payment_type) && !empty($courier_payment_type) && $courier_payment_type != null && $courier_payment_type != "null" && $courier_payment_type != "undefined") {
                $query->where('courier_orders.courier_payment_type_id', $courier_payment_type);
            }
            if (isset($delivery_payment_type) && !empty($delivery_payment_type) && $delivery_payment_type != null && $delivery_payment_type != "null" && $delivery_payment_type != "undefined") {
                $query->where('courier_orders.delivery_payment_type_id', $delivery_payment_type);
            }
            if (isset($date) && !empty($date) && $date != null && $date != "null" && $date != "undefined") {
                $query->whereDate('courier_orders.date', $date);
            } else {
                Session::flash('message', 'Date must be selected!');
                Session::flash('class', 'warning');
                Session::flash('display', 'block');
                return redirect()->back();
            }

            $orders = $query->select(
                'courier_orders.id',
                'courier_orders.azerpost_track',
                'courier_orders.packages',
                'courier_orders.client_id as suite',
                'client.passport_number',
                'client.name as client_name',
                'client.surname as client_surname',
                'courier_orders.phone',
                'courier_areas.name_az as area',
                'courier_regions.name_az as region',
                'courier_metro_stations.name_az as metro_station',
                'courier_orders.address',
                'courier_orders.date',
                'courier_orders.post_zip',
                'courier_payment_types.name_az as courier_payment_type',
                'delivery_payment_types.name_az as delivery_payment_type',
                'courier_orders.delivery_payment_type_id',
                'courier_orders.courier_payment_type_id',
                'courier_orders.is_paid',
                'courier_orders.created_at',
                'courier_orders.amount as delivery_amount',
                'courier_orders.delivery_amount as shipping_amount',
                'courier_orders.total_amount as summary_amount',
                'courier.name as courier_name'
            )
                ->get();

            $orders_count = 0;
            $total_shipping_amount = 0;
            $total_delivery_amount = 0;
            //$total_summary_amount = 0;
           
            foreach ($orders as $order) {
                $orders_count++;
                $total_cash_amount = 0;

                if ($order->is_paid == 0) {
                    $courier_amount = $order->delivery_amount;
                    $shipping_amount = $order->shipping_amount;
                } else {
                    $courier_amount = 0;
                    $shipping_amount = 0;
                }

                if ($order->courier_payment_type_id == 2) {
                    $total_delivery_amount += $courier_amount;
                    $total_cash_amount += $courier_amount;
                }

                if ($order->delivery_payment_type_id == 2) {
                    $total_shipping_amount += $shipping_amount;
                    $total_cash_amount += $shipping_amount;
                }

                    //                if ($order->is_paid == 0 && $order->delivery_payment_type_id == 2) {
                    //                    $total_shipping_amount += $order->shipping_amount;
                    //                    $total_cash_amount += $order->shipping_amount;
                    //                }
                    //                if ($order->is_paid == 0 && $order->courier_payment_type_id == 2) {
                    //                    $total_delivery_amount += $order->delivery_amount;
                    //                    $total_cash_amount += $order->delivery_amount;
                    //                }

                $order->courier_amount = $courier_amount;
                $order->shipping_amount = $shipping_amount;
                $order->total_cash_amount = $total_cash_amount;
                //$total_summary_amount += $order->summary_amount;

                $packages_str = $order->packages;
                $packages_arr = explode(',', $packages_str);
                $packages = Package::whereIn('id', $packages_arr)->select('number')->get();
                $tracks = '';
                foreach ($packages as $package) {
                    $track = $package->number;
                    if (strlen($track) > 7) {
                        $track = substr($track, strlen($track) - 7);
                    }

                    $tracks .= $track . ', ';
                }
                $tracks = trim($tracks);
                if (strlen($tracks) > 0) {
                    $tracks = substr($tracks, 0, -1);
                }

                $order->tracks = $tracks;
            }
            
            $total_summary_amount = $total_delivery_amount + $total_shipping_amount;

            $total_amounts = array();
            $total_amounts['shipping'] = $total_shipping_amount;
            $total_amounts['delivery'] = $total_delivery_amount;
            $total_amounts['summary'] = $total_summary_amount;

            if ($export_type == 3){
                /*if(Auth::id() == 131536	){
                    return view('backend.export.azerpost_courier_new', compact(
                        'orders',
                        'orders_count',
                        'total_amounts',
                        'date',
                    ));
                }*/
                return view('backend.export.azerpost_courier_new', compact(
                    'orders',
                    'orders_count',
                    'total_amounts',
                    'date',
                ));
            }
            return view('backend.export.courier', compact(
                'orders',
                'orders_count',
                'total_amounts',
                'date',
            ));
        } catch (\Exception $e) {
            Session::flash('message', 'An error occurred!');
            Session::flash('class', 'warning');
            Session::flash('display', 'block');
            return redirect()->back();
        }
    }

    public function update_date(Request $request){
         //dd($request->all());

        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date'],
            'track' => ['nullable', 'string'],
            'weight' => ['nullable'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => __('courier.incomplete_information')]);
        }
        try{

            $courier_order = CourierOrders::where('id', $request->id)
                ->whereNull('deleted_by')
                ->select(
                    'packages',
                    'id',
                    'courier_id'
                )
                ->first();
                // dd($courier_order);
                // if($courier_order->courier_id != null){
                //     return response([
                //         'title'=> 'Oops',
                //         'content'=> 'Tarix yenilənmədi! Kuriyer təyin olunub'
                //     ]);
                // }else{  
                    $date = $request->date;
                    $track = $request->track;
                    $weight = $request->weight;
                    $status = CourierOrders::where(['id'=>$request->id])->whereNull('deleted_by')->update([
                        'date'=>$date,
                        'azerpost_track' => $track,
                        'order_weight' => $weight
                    ]);

                   // dd($status);
                    return response([
                        'title'=> 'Success',
                        'case' => 'success',
                        'content'=> 'Tarix uğurla yeniləndi'
                    ]);
                    // return redirect()->route('warehouse_courier_page');
                // }
            
        }catch (\Exception $e) {
            DB::rollBack();
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
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

    public function set_azerpost(Request $request){
        //dd($request->all());
        try {
            $orderId = $request->input('order_id');

            if (count($orderId) > 0){

                $courier_orders = CourierOrders::whereIn('id', $orderId)
                    ->whereNotNull('azerpost_track')
                    ->whereNotNull('order_weight')
                    ->where('courier_id', 144389)
                    ->get();

                //dd($courier_orders);
                DB::table('courier_orders')->whereIn('id', $courier_orders->pluck('id')->toArray())->update([
                    'is_set_azerpost' => 1
                ]);

                return response([
                    'title'=> 'Success',
                    'case' => 'success',
                    'content'=> 'Baglamalar Azerpost ucun hazirlandi'
                ]);

            }else{
                return response([
                    'title'=> 'error',
                    'case' => 'error',
                    'content'=> 'Baglamalar secilmeyib'
                ]);
            }
        }catch (\Exception $exception){
            return response([
                'title'=> 'error',
                'case' => 'error',
                'content'=> 'Bilinmeyen xeta'
            ]);
        }

    }
}
