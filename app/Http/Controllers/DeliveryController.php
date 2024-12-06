<?php

namespace App\Http\Controllers;

use App\CourierOrders;
use App\Package;
use App\PackageStatus;
use App\Receipts;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends HomeController
{
    public function index()
    {
        return view('backend.warehouse.delivery');
    }

    public function get_packages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receipt' => ['required', 'string', 'max:8'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $receipt = $request->receipt;

            $receipts = Receipts::where('receipt', $receipt)
                ->whereNotNull('courier_order_id')
                ->whereNull('deleted_by')
                ->orderBy('id', 'desc')
                ->select('id', 'courier_order_id')
                ->first();

            $is_courier_order = false;

            if (!$receipts) {
                // for delivery
                $packages = Package::leftJoin('currency as cur', 'package.currency_id', '=', 'cur.id')
                    ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
                    ->where('package.payment_receipt', $receipt)
                    //->where(['client.cargo_debt' => 0, 'client.common_debt' => 0]) // change front
                    ->where('package.paid_status', 1)
                    ->whereNull('issued_to_courier_date')
                    ->whereNull('package.delivered_by')
                    ->whereNull('package.deleted_by')
                    ->select(
                        'package.number',
                        'package.internal_id',
                        'package.total_charge_value as amount',
                        'package.paid',
                        'cur.name as currency',
                        'package.client_id',
                        'client.cargo_debt',
                        'client.common_debt'
                    )
                    ->get();
            } else {
                // for courier
                $is_courier_order = true;

                $courier_order_id = $receipts->courier_order_id;
                $order = CourierOrders::where('id', $courier_order_id)
                    ->whereNotNull('courier_orders.courier_id')
                    ->whereRaw('(
                        (courier_orders.courier_payment_type_id = 1 and courier_orders.is_paid = 1) or
                        (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id <> 1) or
                        (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_amount = 0) or
                        (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id = 1 and courier_orders.delivery_amount > 0 and courier_orders.is_paid = 1)
                        )')
                    ->select('packages')
                    ->first();

                if (!$order) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Courier order not found!']);
                }

                $packages_str = $order->packages;
                $packages_arr = explode(',', $packages_str);

                $packages = Package::leftJoin('currency as cur', 'package.currency_id', '=', 'cur.id')
                    ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
                    ->whereIn('package.id', $packages_arr)
                    ->whereNull('package.delivered_by')
                    ->whereNull('package.courier_by')
                    ->whereNull('package.deleted_by')
                    ->whereNotNull('package.issued_to_courier_date')
                    ->select(
                        'package.number',
                        'package.internal_id',
                        'package.total_charge_value as amount',
                        'package.paid',
                        'cur.name as currency',
                        'package.client_id',
                        'client.cargo_debt',
                        'client.common_debt'
                    )
                    ->get();
            }

            if (count($packages) == 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Packages not found!']);
            }

            return response(['case' => 'success', 'packages' => $packages, 'is_courier_order' => $is_courier_order]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function set_delivered(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receipt' => ['required', 'string', 'max:8'],
            'package' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $receipt = $request->receipt;
            $package_number = $request->package;
            $user_id = Auth::id();

            $receipts = Receipts::where('receipt', $receipt)
                ->whereNotNull('courier_order_id')
                ->whereNull('deleted_by')
                ->orderBy('id', 'desc')
                ->select('id', 'courier_order_id')
                ->first();

            //usps control
            $package_number = trim($package_number);
            $package_number = preg_replace('/\s+/', '', $package_number);

            if (!$receipts) {
                // for delivery
                if (substr($package_number, 0, 8) == '42019801') {
                    $package_number_search = substr($package_number, -22);
                    $package = Package::leftJoin('users as client', 'package.client_id', '=', 'client.id')
                        ->where('package.payment_receipt', $receipt)
                        ->whereRaw("(package.number = '" . $package_number_search . "' or package.internal_id = '" . $package_number_search . "')")
                        ->where('package.paid_status', 1)
                        //->where(['client.cargo_debt' => 0, 'client.common_debt' => 0])
                        ->whereNull('issued_to_courier_date')
                        ->whereNull('package.delivered_by')
                        ->whereNull('package.deleted_by')
                        ->orderBy('package.id', 'desc')
                        ->select(
                            'package.id',
                            'package.number',
                            'client.cargo_debt',
                            'client.common_debt',
                            'courier_order_id'
                        )
                        ->first();

                    if (!$package) {
                        $package_number_search = substr($package_number, 10, strlen($package_number) - 1);
                        $package = Package::leftJoin('users as client', 'package.client_id', '=', 'client.id')
                            ->where('package.payment_receipt', $receipt)
                            ->whereRaw("(package.number = '" . $package_number_search . "' or package.internal_id = '" . $package_number_search . "')")
                            ->where('package.paid_status', 1)
                            //->where(['client.cargo_debt' => 0, 'client.common_debt' => 0])
                            ->whereNull('issued_to_courier_date')
                            ->whereNull('package.delivered_by')
                            ->whereNull('package.deleted_by')
                            ->orderBy('package.id', 'desc')
                            ->select(
                                'package.id',
                                'package.number',
                                'client.cargo_debt',
                                'client.common_debt',
                                'courier_order_id'
                            )
                            ->first();
                    }
                } else {
                    $package_number_search = $package_number;
                    $package = Package::leftJoin('users as client', 'package.client_id', '=', 'client.id')
                        ->where('package.payment_receipt', $receipt)
                        ->whereRaw("(package.number = '" . $package_number_search . "' or package.internal_id = '" . $package_number_search . "')")
                        ->where('package.paid_status', 1)
                        //->where(['client.cargo_debt' => 0, 'client.common_debt' => 0])
                        ->whereNull('issued_to_courier_date')
                        ->whereNull('package.delivered_by')
                        ->whereNull('package.deleted_by')
                        ->orderBy('package.id', 'desc')
                        ->select(
                            'package.id',
                            'package.number',
                            'client.cargo_debt',
                            'client.common_debt',
                            'courier_order_id'
                        )
                        ->first();
                }
            } else {
                // for courier
                $courier_order_id = $receipts->courier_order_id;
                $order = CourierOrders::where('id', $courier_order_id)
                    ->whereNotNull('courier_orders.courier_id')
                    ->whereRaw('(
                        (courier_orders.courier_payment_type_id = 1 and courier_orders.is_paid = 1) or
                        (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id <> 1) or
                        (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_amount = 0) or
                        (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id = 1 and courier_orders.delivery_amount > 0 and courier_orders.is_paid = 1)
                        )')
                    ->select('packages')
                    ->first();

                if (!$order) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Courier order not found!']);
                }

                $packages_str = $order->packages;
                $packages_arr = explode(',', $packages_str);

                if (substr($package_number, 0, 8) == '42019801') {
                    $package_number_search = substr($package_number, -22);
                    $package = Package::leftJoin('users as client', 'package.client_id', '=', 'client.id')
                        ->whereRaw("(package.number = '" . $package_number_search . "' or package.internal_id = '" . $package_number_search . "')")
                        ->whereIn('package.id', $packages_arr)
                        ->whereNull('package.delivered_by')
                        ->whereNull('package.courier_by')
                        ->whereNull('package.deleted_by')
                        ->whereNotNull('package.issued_to_courier_date')
                        ->orderBy('package.id', 'desc')
                        ->select('package.id', 'package.number', 'client.cargo_debt', 'client.common_debt')
                        ->first();

                    if (!$package) {
                        $package_number_search = substr($package_number, 10, strlen($package_number) - 1);
                        $package = Package::leftJoin('users as client', 'package.client_id', '=', 'client.id')
                            ->whereRaw("(package.number = '" . $package_number_search . "' or package.internal_id = '" . $package_number_search . "')")
                            ->whereIn('package.id', $packages_arr)
                            ->whereNull('package.delivered_by')
                            ->whereNull('package.courier_by')
                            ->whereNull('package.deleted_by')
                            ->whereNotNull('package.issued_to_courier_date')
                            ->orderBy('package.id', 'desc')
                            ->select('package.id', 'package.number', 'client.cargo_debt', 'client.common_debt')
                            ->first();
                    }
                } else {
                    $package_number_search = $package_number;
                    $package = Package::leftJoin('users as client', 'package.client_id', '=', 'client.id')
                        ->whereRaw("(package.number = '" . $package_number_search . "' or package.internal_id = '" . $package_number_search . "')")
                        ->whereIn('package.id', $packages_arr)
                        ->whereNull('package.delivered_by')
                        ->whereNull('package.courier_by')
                        ->whereNull('package.deleted_by')
                        ->whereNotNull('package.issued_to_courier_date')
                        ->orderBy('package.id', 'desc')
                        ->select('package.id', 'package.number', 'client.cargo_debt', 'client.common_debt')
                        ->first();
                }
            }

            if (!$package) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Package not found!']);
            }

            if ($package->cargo_debt > 0 || $package->common_debt > 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'This client has debt: ' . ($package->cargo_debt + $package->common_debt) . ' TL!']);
            }

            $package_id = $package->id;
            $package_no = $package->number;

            if (!$receipts) {
                // for delivery
                Package::where('id', $package_id)->update(['delivered_by' => $user_id, 'delivered_at' => Carbon::now()]);

                PackageStatus::create([
                    'package_id' => $package_id,
                    'status_id' => 3, //delivered
                    'created_by' => $user_id
                ]);

                // courier order control
                if ($package->courier_order_id != null) {
                    $package_courier_order_id = $package->courier_order_id;

                    $courier_order = CourierOrders::where('id', $package_courier_order_id)
                        ->select('packages')
                        ->first();

                    if ($courier_order) {
                        $old_packages_str = $courier_order->packages;
                        $packages_arr = explode(',', $old_packages_str);

                        $packages_count = 0;
                        $new_packages_str = '';
                        for ($i = 0; $i < count($packages_arr); $i++) {
                            $package_id_for_courier_order = trim($packages_arr[$i]);

                            if ($package_id == $package_id_for_courier_order) {
                                continue;
                            }

                            $new_packages_str .= $package_id_for_courier_order . ',';
                            $packages_count++;
                        }

                        if ($packages_count == 0) {
                            CourierOrders::where('id', $package_courier_order_id)->update([
                                'deleted_by' => Auth::id(),
                                'deleted_at' => Carbon::now()
                            ]);
                        } else {
                            $new_packages_str = substr($new_packages_str, 0, -1);

                            CourierOrders::where('id', $package_courier_order_id)->update(['packages' => $new_packages_str]);
                        }
                    }
                }
            } else {
                // for courier
                Package::where('id', $package_id)->update(['courier_by' => Auth::id(), 'courier_at' => Carbon::now()]);

                PackageStatus::create([
                    'package_id' => $package_id,
                    'status_id' => 30,
                    'created_by' => Auth::id()
                ]);
            }

            return response(['case' => 'success', 'delivered' => 1, 'package' => $package_no]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }
}
