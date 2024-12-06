<?php

namespace App\Http\Controllers;

use App\BalanceLog;
use App\CashierLog;
use App\CourierOrders;
use App\CourierOrderStatus;
use App\ExchangeRate;
use App\Package;
use App\PackageStatus;
use App\PaymentLog;
use App\Receipts;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CourierCashierController extends HomeController
{
    public function get_courier_page()
    {
        return view('backend.cashier.courier');
    }

    public function get_courier_orders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Date not found!']);
        }
        try {
            $date = $request->date;

            $not_delivered_orders = Receipts::leftJoin('courier_orders', 'receipts.courier_order_id', '=', 'courier_orders.id')
                ->leftJoin('courier_payment_types', 'courier_orders.courier_payment_type_id', '=', 'courier_payment_types.id')
                ->leftJoin('courier_payment_types as delivery_payment_types', 'courier_orders.delivery_payment_type_id', '=', 'delivery_payment_types.id')
                ->leftJoin('users as client', 'courier_orders.client_id', '=', 'client.id')
                ->leftJoin('users as courier', 'courier_orders.courier_id', '=', 'courier.id')
                ->leftJoin('lb_status as status', 'courier_orders.last_status_id', '=', 'status.id')
                ->whereDate('courier_orders.date', $date)
                ->whereNotNull('courier_orders.courier_id')
                ->whereNull('courier_orders.delivered_at')
                //->whereNull('receipts.deleted_by')
                ->whereNull('courier_orders.deleted_by')
                ->whereRaw('(
                (courier_orders.courier_payment_type_id = 1 and courier_orders.is_paid = 1) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id <> 1) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_amount = 0) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id = 1 and courier_orders.delivery_amount > 0 and courier_orders.is_paid = 1)
                )')
                ->select(
                    'courier_orders.id',
                    'courier_orders.client_id as suite',
                    'client.name as client_name',
                    'client.surname as client_surname',
                    'courier_orders.courier_payment_type_id',
                    'courier_orders.delivery_payment_type_id as shipping_payment_type_id',
                    'courier_payment_types.name_en as courier_payment_type',
                    'delivery_payment_types.name_en as delivery_payment_type',
                    'courier_orders.amount as courier_amount',
                    'courier_orders.delivery_amount as shipping_amount',
                    'courier_orders.total_amount',
                    'courier_orders.is_paid',
                    'courier.username as courier',
                    'status.status_en as status',
                    'courier_orders.delivered_at'
                )
                ->orderBy('courier_orders.id')
                ->get();

            foreach ($not_delivered_orders as $not_delivered_order) {
                $not_delivered_orders_total_cash_amount_azn = 0;

                if ($not_delivered_order->is_paid == 0) {
                    $courier_amount_azn = $not_delivered_order->courier_amount;
                    $shipping_amount_azn = $not_delivered_order->shipping_amount;
                } else {
                    $courier_amount_azn = 0;
                    $shipping_amount_azn = 0;
                }

//                $courier_amount_azn = $not_delivered_order->courier_amount;
//                $shipping_amount_azn = $not_delivered_order->shipping_amount;

                if ($not_delivered_order->courier_payment_type_id == 2) {
                    $not_delivered_orders_total_cash_amount_azn += $courier_amount_azn;
                }

                if ($not_delivered_order->delivery_payment_type_id == 2) {
                    $not_delivered_orders_total_cash_amount_azn += $shipping_amount_azn;
                }

                $not_delivered_order->summary_amount = $not_delivered_orders_total_cash_amount_azn;
            }

            $delivered_orders = Receipts::leftJoin('courier_orders', 'receipts.courier_order_id', '=', 'courier_orders.id')
                ->leftJoin('courier_payment_types', 'courier_orders.courier_payment_type_id', '=', 'courier_payment_types.id')
                ->leftJoin('courier_payment_types as delivery_payment_types', 'courier_orders.delivery_payment_type_id', '=', 'delivery_payment_types.id')
                ->leftJoin('users as client', 'courier_orders.client_id', '=', 'client.id')
                ->leftJoin('users as courier', 'courier_orders.courier_id', '=', 'courier.id')
                ->leftJoin('lb_status as status', 'courier_orders.last_status_id', '=', 'status.id')
                ->whereDate('courier_orders.date', $date)
                ->whereNotNull('courier_orders.courier_id')
                ->whereNotNull('courier_orders.delivered_at')
                //->whereNull('receipts.deleted_by')
                ->whereNull('courier_orders.deleted_by')
                ->whereRaw('(
                (courier_orders.courier_payment_type_id = 1 and courier_orders.is_paid = 1) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id <> 1) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_amount = 0) or
                (courier_orders.courier_payment_type_id <> 1 and courier_orders.delivery_payment_type_id = 1 and courier_orders.delivery_amount > 0 and courier_orders.is_paid = 1)
                )')
                ->select(
                    'courier_orders.id',
                    'courier_orders.client_id as suite',
                    'client.name as client_name',
                    'client.surname as client_surname',
                    'courier_orders.courier_payment_type_id',
                    'courier_orders.delivery_payment_type_id as shipping_payment_type_id',
                    'courier_payment_types.name_en as courier_payment_type',
                    'delivery_payment_types.name_en as delivery_payment_type',
                    'courier_orders.amount as courier_amount',
                    'courier_orders.delivery_amount as shipping_amount',
                    'courier_orders.total_amount',
                    'courier_orders.is_paid',
                    'courier.username as courier',
                    'status.status_en as status',
                    'courier_orders.delivered_at'
                )
                ->orderBy('courier_orders.id')
                ->get();

            foreach ($delivered_orders as $delivered_order) {
                $delivered_orders_total_cash_amount_azn = 0;

                $courier_amount_azn = $delivered_order->courier_amount;
                $shipping_amount_azn = $delivered_order->shipping_amount;

                if ($delivered_order->courier_payment_type_id == 2) {
                    $delivered_orders_total_cash_amount_azn += $courier_amount_azn;
                }

                if ($delivered_order->delivery_payment_type_id == 2) {
                    $delivered_orders_total_cash_amount_azn += $shipping_amount_azn;
                }

                $delivered_order->summary_amount = $delivered_orders_total_cash_amount_azn;
            }

            return response(['case' => 'success', 'not_delivered_orders' => $not_delivered_orders, 'delivered_orders' => $delivered_orders]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function set_to_paid_and_delivered(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receipt' => ['required', 'string', 'max:20'],
            'date' => ['required', 'date'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Date not found!']);
        }
        try {
            $receipt = $request->receipt;
            $date = $request->date;

            $order = Receipts::leftJoin('courier_orders', 'receipts.courier_order_id', '=', 'courier_orders.id')
                ->where('receipts.receipt', $receipt)
                ->whereDate('courier_orders.date', $date)
                //->whereNull('receipts.deleted_by')
                ->whereNull('courier_orders.deleted_by')
                ->whereNull('courier_orders.canceled_by')
                ->whereNull('courier_orders.delivered_at')
                ->select(
                    'courier_orders.id',
                    'courier_orders.client_id',
                    'courier_orders.amount',
                    'courier_orders.is_paid',
                    'courier_orders.courier_payment_type_id',
                    'courier_orders.delivery_payment_type_id',
                    'courier_orders.delivered_at',
                    'courier_orders.packages'
                )
                ->orderBy('receipts.id', 'desc')
                ->first();

            if (!$order) {
                return response(['case' => 'warning', 'title' => 'Courier order not found!', 'content' => 'Courier order not found! Order canceled or order already delivered!']);
            }

            $courier_order_id = $order->id;

            $today = Carbon::now();
            $rates = ExchangeRate::whereDate('from_date', '<=', $today)->whereDate('to_date', '>=', $today)
                ->select('rate', 'from_currency_id', 'to_currency_id')
                ->get();

            if (count($rates) == 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rates not found!']);
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
                    'created_by' => Auth::id()
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
                ->whereNotNull('courier_by')
                ->whereNull('delivered_by')
                ->whereNotNull('issued_to_courier_date')
                ->select('id', 'total_charge_value as amount', 'paid', 'currency_id', 'paid_status')
                ->get();

            foreach ($packages as $package) {
                if ($order->delivery_payment_type_id != 1 && $package->paid_status == 0) {
                    $package_amount = $package->amount - $package->paid;

                    PaymentLog::create([
                        'payment' => $package_amount,
                        'currency_id' => $package->currency_id,
                        'client_id' => $client_id,
                        'package_id' => $package->id,
                        'type' => $delivery_payment_type_for_payment_log,
                        'created_by' => Auth::id()
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
                    'status_id' => 34, // delivered by courier
                    'created_by' => Auth::id()
                ]);
            }

            Package::whereIn('id', $packages_arr)
                ->whereNotNull('courier_by')
                ->whereNull('delivered_by')
                ->whereNull('deleted_by')
                ->whereNotNull('issued_to_courier_date')
                ->update([
                    'paid' => DB::raw("`total_charge_value`"),
                    'paid_status' => 1,
                    'payment_type_id' => $order->delivery_payment_type_id,
                    'delivered_by' => Auth::id(),
                    'delivered_at' => $today
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
                'status_id' => 34, // delivered by courier
                'created_by' => Auth::id()
            ]);

            $client = User::where('id', $client_id)->select('balance')->first();

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
                    'receipt' => $receipt,
                    'type' => 'Cash (Courier Delivery)',
                    'created_by' => Auth::id()
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
                    'receipt' => $receipt,
                    'type' => 'Cash (Courier Shipping)',
                    'created_by' => Auth::id()
                ]);
            }

            if ($amount_for_cashier_log_pos_azn_courier > 0) {
                $amount_for_cashier_log_pos_usd_courier = $amount_for_cashier_log_pos_azn_courier * $rate_azn_to_usd;
                $amount_for_cashier_log_pos_usd_courier = sprintf('%0.2f', $amount_for_cashier_log_pos_usd_courier);

                CashierLog::create([
                    'payment_azn' => $amount_for_cashier_log_pos_azn_courier,
                    'payment_usd' => $amount_for_cashier_log_pos_usd_courier,
                    'added_to_balance' => 0, //azn
                    'old_balance' => $client->balance, //usd
                    'new_balance' => $client->balance, //usd
                    'client_id' => $client_id,
                    'receipt' => $receipt,
                    'type' => 'POS Term (Courier Delivery)',
                    'created_by' => Auth::id()
                ]);
            }

            if ($amount_for_cashier_log_pos_azn_shipping > 0) {
                $amount_for_cashier_log_pos_usd_shipping = $amount_for_cashier_log_pos_azn_shipping * $rate_azn_to_usd;
                $amount_for_cashier_log_pos_usd_shipping = sprintf('%0.2f', $amount_for_cashier_log_pos_usd_shipping);

                CashierLog::create([
                    'payment_azn' => $amount_for_cashier_log_pos_azn_shipping,
                    'payment_usd' => $amount_for_cashier_log_pos_usd_shipping,
                    'added_to_balance' => 0, //azn
                    'old_balance' => $client->balance, //usd
                    'new_balance' => $client->balance, //usd
                    'client_id' => $client_id,
                    'receipt' => $receipt,
                    'type' => 'POS Term (Courier Shipping)',
                    'created_by' => Auth::id()
                ]);
            }

            $success_message = 'Paid: ' . $total_amount_for_response . ' AZN and Delivered!';

            return response(['case' => 'success', 'content' => $success_message, 'paid_amount' => $total_amount_for_response, 'id' => $courier_order_id]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
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
}
