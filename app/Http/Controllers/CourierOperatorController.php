<?php

namespace App\Http\Controllers;

use App\CourierAreas;
use App\CourierDailyLimits;
use App\CourierMetroStations;
use App\CourierOrders;
use App\CourierOrderStatus;
use App\CourierPaymentTypes;
use App\CourierSettings;
use App\CourierZonePaymentTypes;
use App\ExchangeRate;
use App\Package;
use App\PackageStatus;
use App\Status;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CourierOperatorController extends HomeController
{
    public function get_courier_page()
    {
        try {
            $statuses = Status::where('for_courier', 1)->select('id', 'status_en as name')->get();
            $couriers = User::where('role_id', 8)->whereNull('deleted_by')->select('id', 'name', 'surname')->get();
            $areas = CourierAreas::select('id', 'name_en as name')->get();
            $payment_types = CourierPaymentTypes::select('id', 'name_en as name')->get();

            return view('backend.operator.courier', compact(
                'statuses',
                'couriers',
                'areas',
                'payment_types'
            ));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function set_status_order(Request $request) {
        
        $validator = Validator::make($request->all(), [
            'order_id' => ['required', 'integer'],
            'status_id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $order_id = $request->order_id;
            $status_id = $request->status_id;
            $courier_order = CourierOrders::where('id', $order_id)
                ->whereNull('deleted_by')
                ->select(
                    'packages',
                    'id',
                    'courier_id'
                )
            ->first();
          
            switch ($status_id) {
                case 3: {
                    $status_id = 34;
                    // delivered by courier
                    CourierOrders::where('id', $order_id)
                        ->whereNull('deleted_by')
                        ->update([
                            'canceled_by' => null,
                            'canceled_at' => null,
                            'delivered_at' => Carbon::now()
                        ]);
                } break;
                case 34: {
                    // delivered by courier
                    CourierOrders::where('id', $order_id)
                        ->whereNull('deleted_by')
                        ->update([
                            'canceled_by' => null,
                            'canceled_at' => null,
                            'delivered_at' => Carbon::now()
                        ]);
                } break;
                case 12: {
                    // canceled

                    if($courier_order->courier_id != null){
                        return response([
                            'title'=> 'Oops',
                            'content'=> 'Paket silinmir! Kuriyer teyin olunub'
                        ]);
                    }else{
                        CourierOrders::where('id', $order_id)
                            ->whereNull('deleted_by')
                            ->update([
                                'canceled_by' => Auth::id(),
                                'canceled_at' => Carbon::now(),
                                'courier_id' => null,
                                'collected_by' => null,
                                'collected_at' => null,
                                'delivered_at' => null
                            ]);
    
                        $order = CourierOrders::where('id', $order_id)->select('packages', 'post_zip', 'delivery_longitude', 'delivery_latitude')->first();
                        $packages_arr = explode(',', $order->packages);
    
                        Package::whereIn('id', $packages_arr)->update([
                            'issued_to_courier_date' => null,
                            'courier_order_id' => null,
                            'has_courier' => 0,
                            'has_courier_by' => null,
                            //'has_courier_at' => null,
                            'courier_by' => null,
                            //'courier_at' => null,
                        ]);
    
                        for ($i = 0; $i < count($packages_arr); $i++) {
                            $package_id = $packages_arr[$i];
    
                            PackageStatus::create([
                                'package_id' => $package_id,
                                'status_id' => 33, // cancel courier
                                'created_by' => Auth::id()
                            ]);
                        }

                    }

                    
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
                        // dd($response);

                    }

     
     
                    // ------------------ 189 deleted method end--------------
                    
                } break;
                case 13: {
                    // placed

                    if($courier_order->courier_id != null){
                        return response([
                            'title'=> 'Oops',
                            'content'=> 'Paket silinmir! Kuriyer teyin olunub'
                        ]);
                    }else{

                        CourierOrders::where('id', $order_id)
                            ->whereNull('deleted_by')
                            ->update([
                                'canceled_by' => null,
                                'canceled_at' => null,
                                'courier_id' => null,
                                'collected_by' => null,
                                'collected_at' => null,
                                'delivered_at' => null
                            ]);

                    }

                } break;
                default: {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Status not found!']);
                }
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

    public function get_new_courier_order_page()
    {
        try {
            $areas = CourierAreas::where('active', 1)->select('id', 'name_en as name')->get();
            $metro_stations = CourierMetroStations::select('id', 'name_en as name')->get();

            $courier_settings = CourierSettings::first();

            if (!$courier_settings) {
                return redirect()->route("get_account");
            }

            $closing_time = Carbon::parse($courier_settings->closing_time);
            $now = Carbon::parse(Carbon::now()->toTimeString());

            $diff_time = $now->diffInSeconds($closing_time, false);

            if ($diff_time < 0) {
                // not today
                $min_date = date("Y-m-d", strtotime(date("Y-m-d") . "+1 day"));
                $max_date = date("Y-m-d", strtotime(date("Y-m-d") . "+3 day"));
            } else {
                $min_date = date('Y-m-d');
                $max_date = date("Y-m-d", strtotime(date("Y-m-d") . "+2 day"));
            }

            $amount_for_urgent = $courier_settings->amount_for_urgent;

            return view('backend.operator.new_courier_order', compact(
                'areas',
                'metro_stations',
                'min_date',
                'max_date',
                'amount_for_urgent'
            ));
        } catch (\Exception $exception) {
            return view('backend.error');
        }
    }

    public function get_client_details(Request $request) {
        $validator = Validator::make($request->all(), [
            'client_id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Client ID not found!']);
        }
        try {
            $client_id = $request->client_id;

            $client = User::where(['id'=>$client_id, 'role_id'=>2])
                ->whereNull('deleted_by')
                ->select('id', 'name', 'surname', 'address1 as address', 'phone1 as phone')
                ->first();

            if (!$client) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Client not found!']);
            }

            // show packages
            $users = array();
            array_push($users, $client_id);

            $sub_accounts = User::where('parent_id', $client_id)->whereNull('deleted_by')
                ->select('id')->get();

            $has_sub_accounts = true;

            if (count($sub_accounts) == 0) {
                $has_sub_accounts = false;
            }

            foreach ($sub_accounts as $sub_account) {
                array_push($users, $sub_account->id);
            }

            $packages = Package::leftJoin('courier_payment_types', 'package.payment_type_id', '=', 'courier_payment_types.id')
                ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
                ->whereIn('package.client_id', $users)
                ->where([
                    'package.in_baku' => 1,
                    'package.is_warehouse' => 3,
                    'has_courier' => 0
                ])
                ->whereNull('package.delivered_by')
                ->whereNull('package.deleted_by')
                ->select(
                    'package.id',
                    'package.payment_type_id',
                    'courier_payment_types.name_en as payment_type',
                    'package.number as track',
                    'package.gross_weight',
                    'package.total_charge_value as amount',
                    'package.paid',
                    'package.currency_id',
                    'package.paid_status',
                    'package.client_id',
                    'client.name as client_name',
                    'client.surname as client_surname'
                )
                ->get();

            if (count($packages) == 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'There is no parcel that meets the conditions!']);
            }

            $date = Carbon::now();
            $rates = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
                ->where('to_currency_id', 3) // to AZN
                ->select('rate', 'from_currency_id', 'to_currency_id')
                ->get();

            $has_rate = true;
            if (count($rates) == 0) {
                $has_rate = false;
            }

            $has_referral_packages = false;
            foreach ($packages as $package) {
                if ($package->client_id != $client_id) {
                    $has_referral_packages = true;
                }

                $currency_id = $package->currency_id;

                if ($has_rate) {
                    $rate_to_azn = $this->calculate_exchange_rate($rates, $currency_id, 3);
                } else {
                    $rate_to_azn = 1;
                }
                $amount_azn = ($package->amount - $package->paid) * $rate_to_azn;
                $amount_azn = sprintf('%0.2f', $amount_azn);

                $package->amount = $amount_azn;

                if (strlen($package->track) > 7) {
                    $package->track = substr($package->track, strlen($package->track) - 7);
                }

                if ($package->paid_status == 0) {
                    $package->payment_type = 'Not paid';
                }
            }

            return response(['case' => 'success', 'client' => $client, 'packages' => $packages, 'has_referrals' => $has_sub_accounts, 'has_referral_packages' => $has_referral_packages]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function get_courier_payment_types(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'area_id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Area format is not correct!']);
        }
        try {
            $area_id = $request->area_id;
            $area = CourierAreas::where('id', $area_id)->select('zone_id', 'tariff')->first();

            if (!$area) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Area not found']);
            }

            $zone_id = $area->zone_id;

            $payment_types = CourierZonePaymentTypes::leftJoin('courier_payment_types', 'courier_zone_payment_type.courier_payment_type_id', '=', 'courier_payment_types.id')
                ->where('courier_zone_payment_type.zone_id', $zone_id)
                ->where('courier_zone_payment_type.courier_payment_type_id', '<>', 1) // not online
                ->select('courier_zone_payment_type.courier_payment_type_id as id', 'courier_payment_types.name_en as name')
                ->orderBy('courier_payment_types.name_en')
                ->distinct()
                ->get();

            return response(['case' => 'success', 'payment_types' => $payment_types, 'tariff' => $area->tariff]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function get_delivery_payment_types(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'area_id' => ['required', 'integer'],
            'courier_payment_type' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Area format is not correct!']);
        }
        try {
            $area_id = $request->area_id;
            $courier_payment_type = $request->courier_payment_type;

            $area = CourierAreas::where('id', $area_id)->select('zone_id')->first();

            if (!$area) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Area not found']);
            }

            $zone_id = $area->zone_id;

            $payment_types = CourierZonePaymentTypes::leftJoin('courier_payment_types', 'courier_zone_payment_type.delivery_payment_type_id', '=', 'courier_payment_types.id')
                ->where('courier_zone_payment_type.zone_id', $zone_id)
                ->where('courier_zone_payment_type.courier_payment_type_id', $courier_payment_type)
                ->select('courier_zone_payment_type.delivery_payment_type_id as id', 'courier_payment_types.name_en as name')
                ->orderBy('courier_payment_types.name_en')
                ->distinct()
                ->get();

            return response(['case' => 'success', 'payment_types' => $payment_types]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function create_courier_order(Request $request) {
        $validator = Validator::make($request->all(), [
            'client_id' => ['required', 'integer'],
            'packages_list' => ['required', 'string', 'max:1000'],
            'metro_station_id' => ['nullable', 'integer'],
            'area_id' => ['required', 'integer'],
            'address' => ['required', 'string'],
            'phone' => ['required', 'string', 'max:30'],
            'date' => ['required', 'date'],
            'courier_payment_type_id' => ['required', 'integer'],
            'delivery_payment_type_id' => ['required', 'integer'],
            'urgent_order' => ['nullable', 'bool'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type'=>'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $payment_type_id = $request->payment_type_id;

            if ($payment_type_id == 1) {
                return response(['case' => 'error', 'title' => 'Error!', 'content' => 'This payment type cannot be selected: online!']);
            }

            $client_id = $request->client_id;

            $courier_settings = CourierSettings::first();

            if (!$courier_settings) {
                return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong! (Courier settings)']);
            }

            $closing_time = Carbon::parse($courier_settings->closing_time);
            $now = Carbon::parse(Carbon::now()->toTimeString());

            $diff_time = $now->diffInSeconds($closing_time, false);

            if ($diff_time < 0) {
                // not today
                $min_date = 1;
                $max_date = 3;
            } else {
                $min_date = 0;
                $max_date = 2;
            }

            $today = Carbon::parse(Carbon::today()->toDateString());
            $selected_date = Carbon::parse($request->date);
            $diff_date = $today->diffInDays($selected_date, false);

            if ($diff_date < $min_date || $diff_date > $max_date) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Courier order can be selected for the next 3 days!']);
            }

            $courier_daily_limits = CourierDailyLimits::whereDate('date', $selected_date)->orderBy('id', 'desc')->select('id', 'count', 'used')->first();
            if (!$courier_daily_limits) {
                $limit_residue = 1;
                $limit_id = 0;
                $limit_used = 0;
                $has_limit = false;
            } else {
                $limit_id = $courier_daily_limits->id;
                $limit_count = $courier_daily_limits->count;
                $limit_used = $courier_daily_limits->used;
                $limit_residue = $limit_count - $limit_used;
                $has_limit = true;
            }

            if ($limit_residue <= 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Daily limit exceeded!']);
            }

            $area_id = $request->area_id;
            $area = CourierAreas::where('id', $area_id)->select('zone_id', 'tariff')->first();
            if (!$area) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'The area was not selected!']);
            }
            $zone_id = $area->zone_id;
            $courier_payment_type_id = $request->courier_payment_type_id;
            $delivery_payment_type_id = $request->delivery_payment_type_id;
            $old_packages_str = $request->packages_list;
            $old_packages_arr = explode(',', $old_packages_str);
            unset($request['packages_list']);

            $tariff = $area->tariff;
            $amount = $tariff;

            if (isset($request->urgent_order) && $request->urgent_order == true) {
                $amount_for_urgent = $courier_settings->amount_for_urgent;
                $amount += $amount_for_urgent;
                $request->merge(['urgent'=>1]);
            }

            $type_control = CourierZonePaymentTypes::where([
                'zone_id' => $zone_id,
                'courier_payment_type_id' => $courier_payment_type_id,
                'delivery_payment_type_id' => $delivery_payment_type_id
            ])->select('id')->first();

            if (!$type_control) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'There is no delivery for the types of payment you choose in the region of your choice!']);
            }

            $users = array();
            array_push($users, $client_id);

            $sub_accounts = User::where('parent_id', $client_id)->whereNull('deleted_by')
                ->select('id')->get();

            foreach ($sub_accounts as $sub_account) {
                array_push($users, $sub_account->id);
            }

            $packages = Package::whereNull('deleted_by')
                ->whereIn('package.client_id', $users)
                ->where([
                    'package.in_baku' => 1,
                    'package.is_warehouse' => 3,
                    'has_courier' => 0
                ])
                ->whereNull('package.delivered_by')
                ->whereNull('package.deleted_by')
                ->whereIn('id', $old_packages_arr)
                ->select(
                    'id',
                    'package.total_charge_value as amount',
                    'package.paid',
                    'package.currency_id'
                )
                ->get();

            if (count($packages) == 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'No package meets the requirements!']);
            }

            $date = Carbon::now();
            $rates = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
                ->where('to_currency_id', 3) // to AZN
                ->select('rate', 'from_currency_id', 'to_currency_id')
                ->get();

            $has_rate = true;
            if (count($rates) == 0) {
                $has_rate = false;
            }

            $new_packages_str = '';
            $delivery_amount = 0;
            foreach ($packages as $package) {
                $new_packages_str .= $package->id . ',';

                $currency_id = $package->currency_id;

                if ($has_rate) {
                    $rate_to_azn = $this->calculate_exchange_rate($rates, $currency_id, 3);
                } else {
                    $rate_to_azn = 1;
                }
                $package_amount_azn = ($package->amount - $package->paid) * $rate_to_azn;
                $package_amount_azn = sprintf('%0.2f', $package_amount_azn);

                $delivery_amount += $package_amount_azn;
            }

            $new_packages_str = substr($new_packages_str, 0, -1);
            $new_packages_arr = explode(',', $new_packages_str);

            $request->merge([
                'packages' => $new_packages_str,
                'created_by' => Auth::id(),
                'client_id' => $client_id,
                'amount' => $amount,
                'delivery_amount' => $delivery_amount,
                'total_amount' => $amount + $delivery_amount,
            ]);

            $order = CourierOrders::create($request->all());

            CourierOrderStatus::create([
                'order_id' => $order->id,
                'status_id' => 13,
                'created_by' => Auth::id()
            ]);

            Package::whereIn('id', $new_packages_arr)->update([
                'courier_order_id' => $order->id,
                'has_courier' => 1,
                'has_courier_by' => Auth::id(),
                'has_courier_at' => Carbon::now(),
                'has_courier_type' => 'operator_create_order_' . $order->id
            ]);

            if ($has_limit) {
                $new_used = $limit_used + 1;
                CourierDailyLimits::where('id', $limit_id)->update(['used' => $new_used]);
            }

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successfully']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function delete_courier_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Wrong order format!']);
        }
        try {
            if (Auth::user()->role() == 1 || Auth::id() == 124162) {
                // only admin and nezrin
                $order_id = $request->id;
                $order = CourierOrders::where('id', $order_id)->select('packages')->first();

                if (!$order) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Order not found!']);
                }

                $now = Carbon::now();

                CourierOrders::where('id', $order_id)->update(['deleted_by' => Auth::id(), 'deleted_at' => $now]);

                $packages_arr = explode(',', $order->packages);
                Package::whereIn('id', $packages_arr)->update([
                    'issued_to_courier_date' => null,
                    'courier_order_id' => null,
                    'has_courier' => 0,
                    'has_courier_by' => null,
                    //'has_courier_at' => null,
                    'courier_by' => null,
                    //'courier_at' => null,
                ]);

                for ($i = 0; $i < count($packages_arr); $i++) {
                    $package_id = $packages_arr[$i];

                    PackageStatus::create([
                        'package_id' => $package_id,
                        'status_id' => 33, // cancel courier
                        'created_by' => Auth::id()
                    ]);
                }

                return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successfully!']);
            } else {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Access denied!']);
            }
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
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
}
