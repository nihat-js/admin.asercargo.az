<?php

namespace App\Http\Controllers;

use App\BalanceLog;
use App\CashierLog;
use App\ExchangeRate;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BalanceOperationsController extends HomeController
{
    public function get_client_balance(Request $request) {
        $validator = Validator::make($request->all(), [
            'suite' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Wrong client id!']);
        }
        try {
            $client_id = $request->suite;

            $client = User::where(['id' => $client_id, 'role_id' => 2])->select('balance', 'name', 'surname')->first();

            if (!$client) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Client not found!']);
            }

            $balance_usd = $client->balance;

            $date = Carbon::today();
            $rate = ExchangeRate::whereDate('from_date', '<=', $date)
                ->whereDate('to_date', '>=', $date)
                ->where('from_currency_id', 1)
                ->where('to_currency_id', 3)
                ->select('rate')
                ->orderBy('id', 'desc')
                ->first();

            if (!$rate) {
                // rate note found
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rate not found (USD -> AZN)!']);
            }

            $rate_usd_to_azn = $rate->rate;

            $balance_azn = $balance_usd * $rate_usd_to_azn;
            $balance_azn = sprintf('%0.2f', $balance_azn);

            $client_full_name = $client->name . ' ' . $client->surname;

            return response(['case' => 'success', 'balance_azn' => $balance_azn, 'balance_usd' => $balance_usd, 'client' => $client_full_name]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }

    public function set_client_balance(Request $request) {
        $validator = Validator::make($request->all(), [
            'suite' => ['required', 'integer'],
            'amount' => ['required'],
            'currency' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return response(['case' => 'warning', 'title' => 'Warning!', 'content' => 'Fill all inputs!']);
        }
        try {
            $client_id = $request->suite;
            $amount = $request->amount;
            $currency = $request->currency;
            $date = Carbon::today();

            $client = User::where(['id' => $client_id, 'role_id' => 2])->select('balance')->first();

            if (!$client) {
                return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Client not found!']);
            }

            $old_balance_usd = $client->balance;

            switch ($currency) {
                case 1: {
                    // usd
                    $rate_to_usd = 1;

                    $rate = ExchangeRate::whereDate('from_date', '<=', $date)
                        ->whereDate('to_date', '>=', $date)
                        ->where('from_currency_id', 1)
                        ->where('to_currency_id', 3)
                        ->select('rate')
                        ->orderBy('id', 'desc')
                        ->first();

                    if (!$rate) {
                        // rate note found
                        return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rate not found (USD -> AZN)!']);
                    }

                    $rate_to_azn = $rate->rate;
                } break;
                case 3: {
                    // azn
                    $rate = ExchangeRate::whereDate('from_date', '<=', $date)
                        ->whereDate('to_date', '>=', $date)
                        ->where('from_currency_id', 3)
                        ->where('to_currency_id', 1)
                        ->select('rate')
                        ->orderBy('id', 'desc')
                        ->first();

                    if (!$rate) {
                        // rate note found
                        return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Rate not found (AZN -> USD)!']);
                    }

                    $rate_to_usd = $rate->rate;

                    $rate_to_azn = 1;
                } break;
                default: {
                    return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Wrong currency!']);
                }
            }

            $amount_usd = $amount * $rate_to_usd;
            $amount_usd = sprintf('%0.2f', $amount_usd);

            $amount_azn = $amount * $rate_to_azn;
            $amount_azn = sprintf('%0.2f', $amount_azn);

            $new_balance_usd = $old_balance_usd + $amount_usd;

            if ($new_balance_usd < 0) {
                $new_balance_usd = 0;
            }

            User::where('id', $client_id)->update(['balance' => $new_balance_usd]);

            if ($amount > 0) {
                $status = 'in';
                $response_message = 'Added to balance: ' . $amount_azn . ' AZN (' . $amount_usd . ' USD)';
            } else {
                $status = 'out';
                $response_message = 'Deducted from balance: ' . $amount_azn . ' AZN (' . $amount_usd . ' USD)';
            }

            $payment_code = Str::random(20);
            BalanceLog::create([
                'payment_code' => $payment_code,
                'amount' => abs($amount_usd),
                'amount_azn' => abs($amount_azn),
                'client_id' => $client_id,
                'status' => $status,
                'type' => 'manual',
                'created_by' => Auth::id()
            ]);

            if ($status == 'in') {
                if (Auth::user()->role() == 4) {
                    $receipt = 'manual_by_cashier';
                } else {
                    $receipt = 'manual_by_admin';
                }
                CashierLog::create([
                    'payment_azn' => $amount_azn,
                    'payment_usd' => $amount_usd,
                    'added_to_balance' => $amount_azn, //azn
                    'old_balance' => $old_balance_usd, //usd
                    'new_balance' => $new_balance_usd, //usd
                    'client_id' => $client_id,
                    'receipt' => $receipt,
                    'type' => 'Cash',
                    'created_by' => Auth::id()
                ]);
            }

            return response(['case' => 'success', 'title' => 'Success', 'content' => $response_message]);
        } catch (\Exception $e) {
            return response(['case' => 'error', 'title' => 'Error!', 'content' => 'An error occurred!']);
        }
    }
}
