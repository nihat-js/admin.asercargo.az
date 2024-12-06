<?php

namespace App\Http\Controllers;

use App\BalanceLog;
use App\CashierLog;
use App\CourierOrders;
use App\CourierOrderStatus;
use App\EmailListContent;
use App\ExchangeRate;
use App\Jobs\CollectorInWarehouseJob;
use App\Package;
use App\PackageStatus;
use App\PaymentLog;
use App\Position;
use App\PrintReceiptLog;
use App\PromoCodes;
use App\PromoCodesGroups;
use App\Receipts;
use App\TestLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CashierController extends HomeController
{
    public function index()
    {
        try {
            $date = Carbon::today();
            $usd = 1;
            $azn = 3;

            $rates = ExchangeRate::whereDate('from_date', '<=', $date)
                ->whereDate('to_date', '>=', $date)
                ->whereRaw('(to_currency_id = ? or to_currency_id = ?)', [$usd, $azn]) //to USD and AZN
                ->select('rate', 'from_currency_id', 'to_currency_id')
                ->orderBy('to_currency_id')
                ->get();

            $promo_codes_groups = PromoCodesGroups::whereColumn('count', '>', 'used_count')->orderBy('name')->select('id', 'name')->get();

            return view("backend.cashier.index", compact('rates', 'promo_codes_groups'));
        } catch (\Exception $exception) {
            return redirect()->route("logout");
        }
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
            $receipt = trim($request->receipt);

            if (strlen($receipt) != 8) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Wrong format: Enter receipt (RC12345678) or client ID (CC121514)']);
            }

            $query = Package::leftJoin('currency as cur', 'package.currency_id', '=', 'cur.id')
                ->leftJoin('position as p', 'package.position_id', '=', 'p.id')
                ->leftJoin('lb_status as s', 'package.last_status_id', '=', 's.id')
                ->leftJoin('users as client', 'package.client_id', '=', 'client.id');

            if (substr($receipt, 0, 2) == 'AS') {
                
                $client_id = substr($receipt, -6);

               /* $client_exist = User::where(['id' => $client_id, 'role_id' => 2])->select('id')->first();

                if (!$client_exist) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Receipt not found for this receipt!']);
                }*/
    
                $users[] = (int)$client_id;
    
                $sub_accounts = User::where('parent_id', $client_id)->whereNull('deleted_by')
                    ->select('id')->get();
    
                foreach ($sub_accounts as $sub_account) {
                    $users[] = $sub_account->id;
                }

                $query->whereIn('package.client_id', $users);
            } else {
                // receipt
                $receipt_detail = Receipts::where('receipt', $receipt)
                    ->whereNull('deleted_by')
                    ->orderBy('id', 'desc')
                    ->select('created_by', 'courier_order_id')
                    ->first();

                if ($receipt_detail) {
                    $client_id = $receipt_detail->created_by;
                } else {
                    // $client_id = 0;
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Receipt not found for this receipt!']);
                }

                if ($receipt_detail->courier_order_id != null) {
                    // courier receipt
                    return response(['case' => 'warning', 'title' => 'This is courier receipt!', 'content' => 'This is courier receipt!']);
                    //return $this->set_to_paid_for_courier_order_and_packages($receipt_detail->courier_order_id);
                }
                
                $query->where('package.payment_receipt', $receipt);
            }

            $staffBranch = Auth::user()->branch();
            if (Auth::user()->role() == 1){
                $packages = $query->whereNotNull('package.client_id')
                    ->whereNull('package.delivered_by')
                    ->whereNull('package.deleted_by')
                    ->whereNull('package.issued_to_courier_date')
                    ->where('in_baku', 1)
                    ->whereRaw('(package.is_warehouse = 3 or package.customs_date is not null)')
                    ->orderBy('package.client_id')
                    ->orderBy('package.paid_status')
                    ->orderBy('package.position_id')
                    ->select(
                        'package.id',
                        'package.number',
                        'package.internal_id',
                        'total_charge_value as amount',
                        'package.amount_usd',
                        'package.amount_azn',
                        'package.paid',
                        'package.paid_sum',
                        'package.paid_azn',
                        'package.paid_status',
                        'package.currency_id',
                        'cur.name as currency',
                        'p.name as position',
                        's.status_en as status',
                        'package.client_id',
                        'client.name as client_name',
                        'client.surname as client_surname',
                        'client.cargo_debt',
                        'client.common_debt',
                        'package.external_w_debt',
                        'package.external_w_debt_day',
                        'package.external_w_debt_azn',
                        'package.internal_w_debt',
                        'package.internal_w_debt_day',
                        'package.internal_w_debt_usd',
                    )
                    ->get();
            }else{
                $packages = $query->whereNotNull('package.client_id')
                    ->whereNull('package.delivered_by')
                    ->whereNull('package.deleted_by')
                    ->whereNull('package.issued_to_courier_date')
                    ->where('package.in_baku', 1)
                    ->whereRaw('(package.is_warehouse = 3 or package.customs_date is not null)')
                    ->where('package.branch_id', $staffBranch)
                    ->orderBy('package.client_id')
                    ->orderBy('package.paid_status')
                    ->orderBy('package.position_id')
                    ->select(
                        'package.id',
                        'package.number',
                        'package.internal_id',
                        'total_charge_value as amount',
                        'package.amount_usd',
                        'package.amount_azn',
                        'package.paid',
                        'package.paid_sum',
                        'package.paid_azn',
                        'package.paid_status',
                        'package.currency_id',
                        'cur.name as currency',
                        'p.name as position',
                        's.status_en as status',
                        'package.client_id',
                        'client.name as client_name',
                        'client.surname as client_surname',
                        'client.cargo_debt',
                        'client.common_debt',
                        'package.external_w_debt',
                        'package.external_w_debt_day',
                        'package.external_w_debt_azn',
                        'package.internal_w_debt',
                        'package.internal_w_debt_day',
                        'package.internal_w_debt_usd',
                    )
                    ->get();
            }
            

            if (count($packages) == 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Packages not found!']);
            }

            $date = Carbon::today();
            $rates = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
                ->select('rate', 'from_currency_id', 'to_currency_id')
                ->get();
            if (!$rates) {
                // rate note found
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rate not found!']);
            }

            foreach ($packages as $package) {
                $currency_id = $package->currency_id;
                /*$pay = $package->amount - $package->paid; // original currency
                $rate_to_azn = $this->calculate_exchange_rate($rates, $currency_id, 3);
                $pay_azn = ($pay * $rate_to_azn) + $package->internal_w_debt + $package->external_w_debt_azn ;
                $pay_azn = sprintf('%0.2f', $pay_azn);
                $package->pay_azn = $pay_azn;
                $rate_to_usd = $this->calculate_exchange_rate($rates, $currency_id, 1);
                $pay_usd = ($pay * $rate_to_usd) + $package->internal_w_debt_usd + $package->external_w_debt;
                $package->pay_usd = $pay_usd;*/

                $internal_debt_day = $package->internal_w_debt_day;
                $external_debt_day = $package->external_w_debt_day;
                $internal_w_debt_azn = $package->internal_w_debt;
                $internal_w_debt_usd = $package->internal_w_debt_usd;
                $external_w_debt_usd = $package->external_w_debt;
                $external_w_debt_azn = $package->external_w_debt_azn;
                $paid_azn = $package->paid_azn;
                $paid_usd = $package->paid_sum;
                $allDebtUsd = $package->amount_usd + $internal_w_debt_usd + $external_w_debt_usd;
                $allDebtAzn = $package->amount_azn + $internal_w_debt_azn + $external_w_debt_azn;

                $pay_azn = $allDebtAzn - $paid_azn; // original currency
                $pay_azn = sprintf('%0.2f', $pay_azn);
                $package->pay_azn = $pay_azn;

                $pay_usd = $allDebtUsd - $paid_usd;
                $pay_usd = sprintf('%0.2f', $pay_usd);
                $package->pay_usd = $pay_usd;


                $internalDebt = ($internal_w_debt_usd !== null && $internal_w_debt_usd !== 0) ? $internal_w_debt_azn . " ₼ (" . ($internal_debt_day - 1) . " gün)" : null;
                $externalDebt = ($external_w_debt_usd !== null && $external_w_debt_usd !== 0) ? $external_w_debt_usd . " $ (" . ($external_debt_day - 1) . " gün)" : null;

                $combinedDebts = '';
                if ($internalDebt !== null) {
                    $combinedDebts .= "Internal: " . $internalDebt;
                }
                if ($externalDebt !== null) {
                    $combinedDebts .= ($combinedDebts !== '') ? ', ' : '';
                    $combinedDebts .= "External: " . $externalDebt;
                }
                $package->combinedDebts = $combinedDebts;


            }

            $rate_usd_azn = $this->calculate_exchange_rate($rates, 1, 3);

            $client = User::where('id', $client_id)->select('id', 'name', 'surname', 'passport_series', 'passport_number', 'passport_fin', 'balance', 'phone1 as phone', 'address1 as address', 'cargo_debt', 'common_debt')->first();
            $client_balance = $client->balance * $rate_usd_azn;
            $client->balance = sprintf('%0.2f', $client_balance);

            return response(['case' => 'success', 'packages' => $packages, 'client' => $client]);
        } catch (\Exception $exception) {
            //dd($exception);
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

    public function set_to_paid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'packages.*' => ['required'],
            'client_id' => ['required', 'integer'],
            'type' => ['required', 'integer'],
            'total_amount' => ['required'], //paid amount (AZN)
            'receipt' => ['required', 'string', 'max:8'],
            'promo_code' => ['nullable', 'string', 'max:15'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $payment_type = $request->type;
            $type_for_cashier_log = 'MIX';
            $user_id = Auth::id();
            if ($payment_type == 1) {
                $type_text = 'Nagd';
                $type_for_balance = 'cash';
                $type_for_cashier_log = 'Cash';
                $payment_type_id = 2;
            } else if ($payment_type == 2) {
                $type_text = 'Kart';
                $type_for_balance = 'cart';
                $type_for_cashier_log = 'POS Term';
                $payment_type_id = 3;
            } else {
                $type_text = 'Balansdan';
                $type_for_balance = 'balance';
                $payment_type_id = 1;
            }

            $packages = json_decode($request->packages);
            $response = array();
            $total = 0; // usd
            $total_azn = 0; // azn

            $client_id = $request->client_id;
            $paid_count = 0;

            $date = Carbon::today();
            $rates = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
                ->select('rate', 'from_currency_id', 'to_currency_id')
                ->get();
            if (!$rates) {
                // rate note found
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rate not found!']);
            }

            $rate_usd_azn = $this->calculate_exchange_rate($rates, 1, 3);
            if ($rate_usd_azn == 0) {
                // rate note found
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rate not found (USD -> AZN)!']);
            }

            $user = User::where('id', $client_id)->select('balance', 'language', 'name', 'surname', 'email')->first();
            if ($user) {
                $user_old_balance = $user->balance;
                $user_old_balance_azn = $user_old_balance * $rate_usd_azn;
            } else {
                return response(['case' => 'warning', 'message' => 'Client not found!']);
            }

            $promo_code_id = 0;
            $promo_code_group_id = 0;
            $discount_percent = 0; // %
            $promo_code = '';
            if (isset($request->promo_code) && !empty($request->promo_code) && $request->promo_code != null && $request->promo_code != "null") {
                $promo_code = $request->promo_code;
                $promo_codes = PromoCodes::where('code', $promo_code)->whereNull('client_id')->select('id', 'percent', 'group_id')->first();

                if (!$promo_codes) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Promo code not found or already used!']);
                }

                if (PromoCodes::where(['code' => $promo_code, 'client_id' => $client_id])->select('id')->first()) {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'This promo code was previously used for this client!']);
                }

                $promo_code_id = $promo_codes->id;
                $promo_code_group_id = $promo_codes->group_id;
                $discount_percent = $promo_codes->percent;
            }

            $user_paid = sprintf('%0.2f', $request->total_amount); // azn
            $user_paid_usd = $user_paid / $rate_usd_azn; // azn to usd
            $user_paid_usd = sprintf('%0.2f', $user_paid_usd);
            
            $all_pay = $user_old_balance_azn + $user_paid;
            $all_pay = sprintf('%0.2f', $all_pay);

            $total_real_price = 0; // for promo code
            $total_discount = 0; // for promo code
            $total_discounted_price = 0; // for promo code

            for ($i = 0; $i < count($packages); $i++) {
                $package = $packages[$i];
                $package_id = $package->id;

                $package_exist = Package::leftJoin('users as client', 'package.client_id', '=', 'client.id')
                    ->where(['package.id' => $package_id])
                    ->where(['client.cargo_debt' => 0, 'client.common_debt' => 0])
                    ->whereNotNull('package.client_id')
                    ->whereNull('package.issued_to_courier_date')
                    ->select(
                        'package.position_id',
                        'package.paid_status',
                        'package.total_charge_value as amount',
                        'package.paid',
                        'package.currency_id',
                        'package.client_id',
                        'client.name as client_name',
                        'client.surname as client_surname',
                        'package.gross_weight',
                        'package.volume_weight',
                        'package.chargeable_weight',
                        'courier_order_id',
                        'package.external_w_debt',
                        'package.external_w_debt_day',
                        'package.external_w_debt_azn',
                        'package.internal_w_debt',
                        'package.internal_w_debt_day',
                        'package.internal_w_debt_usd',
                        'package.paid_sum',
                        'package.paid_azn',
                    )
                    ->orderBy('package.client_id')
                    ->first();

                if (!$package_exist) {
                    continue;
                }

                if ($package_exist->paid_status == 1) {
                    Package::where('id', $package_id)->update(['payment_receipt' => $request->receipt, 'payment_receipt_date' => Carbon::now()]);
                    $position_for_online = Position::where('id', $package_exist->position_id)->select('name')->first();
                    if ($position_for_online) {
                        $position_name_for_online = $position_for_online->name;
                    } else {
                        $position_name_for_online = '---';
                    }
                    $package_response = array();
                    $package_response['number'] = $package->number;
                    $package_response['position'] = $position_name_for_online;
                    $package_response['tip'] = 0;
                    $package_response['send_email'] = false;
                    array_push($response, $package_response);
                    continue;
                }

                $amount = $package_exist->amount;

                $paid = $package_exist->paid;
                $pay_not_discount = $amount - $paid;

                $discounted_amount = ($amount * $discount_percent) / 100; // discounted amount orginal currency
                $amount = $amount - $discounted_amount;
                $amount = sprintf('%0.2f', $amount);
                $discounted_amount_usd = ($package->amount_usd * $discount_percent) / 100; // discounted amount usd
                $amount_usd = $package->amount_usd - $discounted_amount_usd;
                $amount_usd = sprintf('%0.2f', $amount_usd);
                $discounted_amount_azn = ($package->amount_azn * $discount_percent) / 100; // discounted amount azn
                $amount_azn = $package->amount_azn - $discounted_amount_azn;
                $amount_azn = sprintf('%0.2f', $amount_azn);

                $currency_id = $package_exist->currency_id;

                $internal_w_debt_azn = $package->internal_w_debt;
                $internal_w_debt_usd = $package->internal_w_debt_usd;
                $external_w_debt_usd = $package->external_w_debt;
                $external_w_debt_azn = $package->external_w_debt_azn;
                $paid_azn = $package->paid_azn;
                $paid_usd = $package->paid_sum;
                $allDebtUsd = $amount_usd + $internal_w_debt_usd + $external_w_debt_usd;
                $allDebtAzn = $amount_azn + $internal_w_debt_azn + $external_w_debt_azn;

                $pay_azn = $allDebtAzn - $paid_azn;
                $pay_azn = sprintf('%0.2f', $pay_azn);

                $pay_usd = $allDebtUsd - $paid_usd;
                $pay_usd = sprintf('%0.2f', $pay_usd);

                if ($currency_id != 1){
                    $rateToOrginalCurrency = $this->calculate_exchange_rate($rates, 1, $currency_id);
                    $pay = (($internal_w_debt_usd + $external_w_debt_usd) * $rateToOrginalCurrency) + $amount;
                    $pay = $pay - $paid;
                    $pay = sprintf('%0.2f', $pay);
                }else{
                    $pay = $pay_usd;
                }


                if ($pay < 0) {
                    $pay = 0;
                }

                $position = $package->position;

                if ($position == null) {
                    $position = '---';
                }

                /*if ($currency_id != 1) {
                    // currency != USD
                    $rate_to_usd = $this->calculate_exchange_rate($rates, $currency_id, 1);
                    if ($rate_to_usd == 0) {
                        // rate note found
                        return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rate not found (Convert to USD)!']);
                    }

                    $pay_usd = $pay * $rate_to_usd;
                    //$amount_usd = $amount * $rate_to_usd;
                } else {
                    $pay_usd = $pay;
                    //$amount_usd = $amount;
                }*/

                $rate_to_azn = $this->calculate_exchange_rate($rates, $currency_id, 3);
                if ($rate_to_azn == 0) {
                    // rate note found
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rate not found (Convert to AZN)!']);
                }
                //$pay_azn = $pay * $rate_to_azn;
                //$pay_azn = sprintf('%0.2f', $pay_azn);
                //dd($pay_azn, $package->pay_azn);
                $total_discounted_price += $pay_azn;
                $pay_not_discount_azn = $package->pay_azn;
                $pay_not_discount_azn = sprintf('%0.2f', $pay_not_discount_azn);
                $total_real_price += $pay_not_discount_azn;
                //$amount_azn = $amount * $rate_to_azn;
                //$amount_azn = sprintf('%0.2f', $amount_azn);

                //$discounted_amount_azn = $discounted_amount * $rate_to_azn;
                //$discounted_amount_azn = sprintf('%0.2f', $discounted_amount_azn);
                $total_discount += $discounted_amount_azn;

                // Insufficient control
                $old_total = $total;
                $old_total_azn = $total_azn;
                $total = $total + $pay_usd;
                $total_azn = $total_azn + $pay_azn;
                $all_pay = (float)$all_pay;
                $total_azn = (float)$total_azn;
                if ($promo_code_id == 0) {
                    $bug_amount = 0.01;
                } else {
                    $bug_amount = 0.02;
                }
                if (($total_azn - $all_pay) > $bug_amount) {
                    TestLog::create([
                        'tracks' => "break",
                        'ids' => "client_id: " . $client_id . "; package_id: " . $package_id . "; Old total_azn: " . $old_total_azn . "; Total azn: " . $total_azn . "; All pay: " . $all_pay . "; Bug amount: " . $bug_amount
                    ]);
                    $total = $old_total;
                    $total_azn = $old_total_azn;
                    break;
                }

                if ($pay > 0) {
                    PaymentLog::create([
                        'payment' => $pay,
                        'currency_id' => $currency_id,
                        'client_id' => $client_id,
                        'package_id' => $package_id,
                        'type' => $payment_type,
                        'created_by' => $user_id
                    ]);
                }

//                $total_paid = $paid + $pay;
//                $total_paid = sprintf('%0.2f', $total_paid);
//
//                if ($total_paid >= $amount) {
//                    $paid_status = 1;
//                    //$residue = 0;
//                } else {
//                    $paid_status = 0;
//                    //$residue = $amount - $total_paid;
//                }

                $amount_not_discount = $pay + $paid;
                $amount_not_discount_usd = $pay_usd + $paid_usd;
                $amount_not_discount_azn = $pay_azn + $paid_azn;
                $paid_status = 1;

                Package::where('id', $package_id)
                    ->update([
                        'paid' => $amount_not_discount,
                        'paid_sum' => $amount_not_discount_usd,
                        'paid_azn' => $amount_not_discount_azn,
                        'discounted_amount' => $discounted_amount,
                        'promo_code' => $promo_code,
                        'paid_status' => $paid_status,
                        'payment_type_id' => $payment_type_id
                    ]);
                //dd($amount_not_discount, $amount_not_discount_usd, $amount_not_discount_azn);
                // courier order control
                if ($package_exist->courier_order_id != null) {
                    $courier_order_id = $package_exist->courier_order_id;

                    $courier_order = CourierOrders::where('id', $courier_order_id)
                        ->select('delivery_amount', 'total_amount')
                        ->first();

                    if ($courier_order) {
                        $old_delivery_amount = $courier_order->delivery_amount;
                        $old_total_amount = $courier_order->total_amount;
  
                        $new_delivery_amount = $old_delivery_amount - $pay_not_discount_azn;
                        if ($new_delivery_amount < 0 ) {
                            $new_delivery_amount = 0;
                        }

                        $new_total_amount = $old_total_amount - $pay_not_discount_azn;
                        if ($new_total_amount < 0) {
                            $new_total_amount = 0;
                        }

                        $courier_order_update_arr = array();
                        $courier_order_update_arr['delivery_amount'] = $new_delivery_amount;
                        $courier_order_update_arr['total_amount'] = $new_total_amount;

                        if ($new_delivery_amount == 0) {
                            $courier_order_update_arr['delivery_payment_type_id'] = 1; // online
                        }

                        CourierOrders::where('id', $courier_order_id)->update($courier_order_update_arr);
                    }
                }
                
                $package_response = array();
                //$package_response['number'] = $package->number;
                $internal_id = $package->internal_id;
                if (strlen($internal_id) > 3) {
                    $internal_id = str_replace('ASR', '', $internal_id);
                    $internal_id = str_replace('asr', '', $internal_id);
                    $package_response['number'] = $internal_id;
                } else {
                    $package_response['number'] = $package->number;
                }

                $package_response['position'] = $position;
                $package_response['paid'] = $pay_azn;
                $package_response['amount'] = $amount_azn;
                $package_response['tip'] = 1;
                $package_response['suite'] = $package_exist->client_id;
                $package_response['client'] = $package_exist->client_name . ' ' . $package_exist->client_surname;

                $weight_type = $package_exist->chargeable_weight;
                if ($weight_type == 2) {
                    // volume
                    $weight = $package_exist->volume_weihght;
                } else {
                    // gross
                    $weight = $package_exist->gross_weight;
                }

                $package_response['weight'] = $weight;
                $package_response['tracking'] = $package->number;
                $package_response['send_email'] = true;
                array_push($response, $package_response);
                $paid_count++;
            }

            if ($paid_count == 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'No unpaid packages!']);
            }

            if ($user_paid > 0) {
                $payment_code = Str::random(20);
                BalanceLog::create([
                    'payment_code' => $payment_code,
                    'amount' => $user_paid_usd,
                    'amount_azn' => $user_paid,
                    'client_id' => $client_id,
                    'status' => 'in',
                    'type' => $type_for_balance,
                    'created_by' => $user_id
                ]);
            }

            $total = sprintf('%0.2f', $total);
            $total_azn = sprintf('%0.2f', $total_azn);
            $payment_code = Str::random(20);
            BalanceLog::create([
                'payment_code' => $payment_code,
                'amount' => $total,
                'amount_azn' => $total_azn,
                'client_id' => $client_id,
                'status' => 'out',
                'type' => 'balance'
            ]);

            $rest = $user_paid - $total_azn;
            $rest = sprintf('%0.2f', $rest);

            if ($rest <= 0) {
                $rest = 0;
            }

            $user_new_balance_azn = $all_pay - $total_azn;
            if ($user_new_balance_azn < 0 || $total_azn < 0) {
                TestLog::create([
                    'tracks' => "Client: " . $client_id . "; Balance: " . $user_new_balance_azn,
                    'ids' => "Old balance: " . $user_old_balance_azn . "; Total: " . $total_azn . "; User paid: " . $user_paid
                ]);
                $user_new_balance_azn = 0;
            }

            $user_new_balance = $user_new_balance_azn / $rate_usd_azn;
            $user_new_balance = sprintf('%0.2f', $user_new_balance);
           
            User::where('id', $client_id)->update(['balance' => $user_new_balance]);

            if ($payment_type == 1 || $payment_type == 2) {
                CashierLog::create([
                    'payment_azn' => $user_paid,
                    'payment_usd' => $user_paid_usd,
                    'added_to_balance' => $rest, //azn
                    'old_balance' => $user_old_balance, //usd
                    'new_balance' => $user_new_balance, //usd
                    'client_id' => $client_id,
                    'receipt' => $request->receipt,
                    'type' => $type_for_cashier_log,
                    'created_by' => Auth::id()
                ]);
            }

            if ($promo_code_id > 0) {
                PromoCodes::where('id', $promo_code_id)->update([
                    'client_id' => $client_id,
                    'used_at' => Carbon::now(),
                    'real_price' => $total_real_price,
                    'discount' => $total_discount,
                    'discounted_price' => $total_discounted_price,
                    'currency_id' => 3
                ]);
            }

            if ($promo_code_group_id > 0) {
                PromoCodesGroups::where('id', $promo_code_group_id)->increment('used_count');
            }

            $queueController = new \App\Http\Controllers\Api\QueueController();
            $generate_queue = $queueController->create_queue($client_id, 'd', Auth::user()->location(), 'id');

            $email = EmailListContent::where(['type' => 'paid_from_balance_cashier'])->first();

            if ($email) {
                $client = $user->name . ' ' . $user->surname;
                $email_to = $user->email;
                $lang = strtolower($user->language);

                $email_title = $email->{'title_' . $lang}; //from
                $email_subject = $email->{'subject_' . $lang};
                $email_bottom = $email->{'content_bottom_' . $lang};
                $email_content = $email->{'content_' . $lang};
                $email_list_inside = $email->{'list_inside_' . $lang};

                $email_content = str_replace('{name_surname}', $client, $email_content);

                $list_insides = '';

                for ($i = 0; $i < count($response); $i++) {
                    $no = $i + 1;
                    $package_for_email = $response[$i];

                    if ($package_for_email['send_email'] == false) {
                        continue;
                    }

                    $track = $package_for_email['tracking'];
                    $weight = $package_for_email['weight'] . ' kg';
                    $amount_for_email = $package_for_email['paid'] . ' AZN';

                    $list_inside = $email_list_inside;

                    $list_inside = str_replace('{no}', $no, $list_inside);
                    $list_inside = str_replace('{tracking_number}', $track, $list_inside);
                    $list_inside = str_replace('{weight}', $weight, $list_inside);
                    $list_inside = str_replace('{amount}', $amount_for_email, $list_inside);

                    $list_insides .= $list_inside;

                    unset($response[$i]['tracking']);
                    unset($response[$i]['weight']);
                }

                $email_content = str_replace('{list_inside}', $list_insides, $email_content);

                $job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom))
                    ->delay(Carbon::now()->addSeconds(10));
                dispatch($job);
            }

            return response(['case' => 'success', 'packages' => $response, 'total' => $total, 'payment_type' => $type_text, 'queue' => $generate_queue, 'debt' => $total_azn, 'pay' => $user_paid, 'rest' => $rest]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    public function add_print_receipt_log(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'integer'],
            'text' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            PrintReceiptLog::create([
                'text' => $request->text,
                'status' => $request->status, // 1 - success; 0 - error
                'created_by' => Auth::id()
            ]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!']);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }

    private function set_to_paid_for_courier_order_and_packages($courier_order_id)
    {
        try {
            $order = CourierOrders::where('id', $courier_order_id)
                ->whereNull('deleted_by')
                ->whereNull('canceled_by')
                ->select('id', 'client_id', 'amount', 'is_paid', 'courier_payment_type_id', 'delivery_payment_type_id', 'delivered_at', 'packages')
                ->first();

            if (!$order) {
                return response(['case' => 'warning', 'title' => 'Courier order not found!', 'content' => 'Courier order not found or order has been canceled.']);
            }

            $date = Carbon::now();
            $rates = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
                ->select('rate', 'from_currency_id', 'to_currency_id')
                ->get();

            $has_rate = true;
            if (count($rates) == 0) {
                $has_rate = false;
            }

            $client_id = $order->client_id;
            $packages_str = $order->packages;
            $packages_arr = explode(',', $packages_str);
            $total_amount_azn = 0;

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
                        }
                        break;
                    default:
                    {
                        // cash (2)
                        $courier_payment_type_for_payment_log = 1;
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

            if ($order->delivery_payment_type_id != 1) {
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
                    ->where('paid_status', 0)
                    ->select('id', 'total_charge_value as amount', 'paid', 'currency_id', 'paid_status')
                    ->get();

                foreach ($packages as $package) {
                    if ($package->paid_status == 0) {
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
                        if ($has_rate) {
                            $rate_to_azn = $this->calculate_exchange_rate($rates, $currency_id, 3);
                        } else {
                            $rate_to_azn = 1;
                        }
                        $package_amount_azn = $package_amount * $rate_to_azn;

                        $total_amount_azn += $package_amount_azn;
                    }

                    PackageStatus::create([
                        'package_id' => $package->id,
                        'status_id' => 3, // delivered
                        'created_by' => Auth::id()
                    ]);
                }
            }

            Package::whereIn('id', $packages_arr)
                //->where('paid_status', 0)
                ->update([
                    'paid' => DB::raw("`total_charge_value`"),
                    'paid_status' => 1,
                    'payment_type_id' => $order->delivery_payment_type_id,
                    'delivered_by' => Auth::id(),
                    'delivered_at' => $date
                ]);

            if ($has_rate) {
                $rate_azn_to_usd = $this->calculate_exchange_rate($rates, 3, 1);
            } else {
                $rate_azn_to_usd = 1;
            }
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
                'status_id' => 3,
                'created_by' => Auth::id()
            ]);

            $success_message = 'Paid: ' . $total_amount_azn . ' AZN';

            return response(['case' => 'success', 'courier' => 'yes', 'content' => $success_message]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong (pay courier order)!']);
        }
    }

    public function get_promo_code (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $group_id = $request->group_id;

            $promo_code = PromoCodes::leftJoin('promo_codes_groups', 'promo_codes.group_id', '=', 'promo_codes_groups.id')
                ->where('promo_codes.group_id', $group_id)
                ->where(function($query) {
                    $query->where('promo_codes.reserved_at', '<', Carbon::now()->addMinutes(-3))
                        ->orWhereNull('promo_codes.reserved_at');
                })
                ->whereNull('promo_codes_groups.deleted_by')
                ->whereNull('promo_codes.client_id')
                ->select('promo_codes.id', 'promo_codes.code', 'promo_codes.percent')
                ->first();

            if (!$promo_code) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Promo code not found!']);
            }

            $id = $promo_code->id;
            $code = $promo_code->code;
            $percent = $promo_code->percent;

            PromoCodes::where('id', $id)->update(['reserved_at' => Carbon::now(), 'reserved_by' => Auth::id()]);

            return response(['case' => 'success', 'title' => 'Success!', 'content' => 'Successful!', 'promo_code' => $code, 'percent' => $percent]);
        } catch (\Exception $exception) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }
    
    public function qmatic_print(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
        }
        try {
            $suite= null;
            if (strtoupper(substr($request->client_id, 0, 2)) === 'AS') {
                $suite = (int)substr($request->client_id, 2);
            }else{
                $suite = $request->client_id;
            }
            
            $client_id = $suite;
            
            $query = Package::leftJoin('currency as cur', 'package.currency_id', '=', 'cur.id')
                ->leftJoin('position as p', 'package.position_id', '=', 'p.id')
                ->leftJoin('lb_status as s', 'package.last_status_id', '=', 's.id')
                ->leftJoin('users as client', 'package.client_id', '=', 'client.id');
            
           
                
            //$query->where('package.client_id', $client_id);
    
            $users[] = (int)$client_id;
    
            $sub_accounts = User::where('parent_id', $client_id)->whereNull('deleted_by')
                ->select('id')->get();
    
            foreach ($sub_accounts as $sub_account) {
                $users[] = $sub_account->id;
            }

            $staffId = Auth::user()->id;
            $staffBranch = Auth::user()->branch();
            if (Auth::user()->role() == 1){
                $packages = $query->whereNotNull('package.client_id')
                    ->whereIn('package.client_id', $users)
                    ->whereNull('package.delivered_by')
                    ->whereNull('package.deleted_by')
                    ->whereNull('package.issued_to_courier_date')
                    ->where('in_baku', 1)
                    ->whereRaw('(package.is_warehouse = 3 or package.customs_date is not null)')
                    ->orderBy('package.client_id')
                    ->orderBy('package.paid_status')
                    ->orderBy('package.position_id')
                    ->select(
                        'package.id',
                        'package.internal_id',
                        'p.name as position',
                        'package.client_id',
                        DB::raw("CONCAT(client.name, ' ', client.surname) as client_name")
                    )
                    ->get();
            }else{
                $packages = $query->whereNotNull('package.client_id')
                    ->whereIn('package.client_id', $users)
                    ->whereNull('package.delivered_by')
                    ->whereNull('package.deleted_by')
                    ->whereNull('package.issued_to_courier_date')
                    ->where('package.in_baku', 1)
                    ->whereRaw('(package.is_warehouse = 3 or package.customs_date is not null)')
                    ->where('package.branch_id', $staffBranch)
                    ->orderBy('package.client_id')
                    ->orderBy('package.paid_status')
                    ->orderBy('package.position_id')
                    ->select(
                        'package.id',
                        'package.internal_id',
                        'p.name as position',
                        'package.client_id',
                        DB::raw("CONCAT(client.name, ' ', client.surname) as client_name")
                    )
                    ->get();
            }
            
            
            if (count($packages) == 0) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Packages not found!']);
            }
    
            $date = Carbon::now()->toDateTimeString();
            $time = time();
            $receipt = 'RC' . substr($time, -5) . rand(0, 9);
    
            while (Receipts::where('receipt', $receipt)->select('id')->first()) {
                $receipt = 'RC' . substr($time, -5) . rand(0, 9);
            }
    
            Receipts::create([
                'receipt' => $receipt,
                'created_by' => $staffId
            ]);
    
            $client = User::where('id', $client_id)->select('id', 'name', 'surname')->first();
    
            $packageIds = $packages->pluck('id')->toArray();
            Package::whereIn('id', $packageIds)
                ->whereNull('issued_to_courier_date')
                ->update(['payment_receipt' => $receipt, 'payment_receipt_date' => $date]);
            
            return response(['case' => 'success', 'packages' => $packages, 'client' => $client, 'receipt' => $receipt]);
        } catch (\Exception $exception) {
            //dd($exception);
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, something went wrong!']);
        }
    }
}
