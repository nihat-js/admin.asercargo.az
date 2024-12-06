<?php

namespace App\Http\Controllers\Api;

use App\ApiUser;
use App\ApiUsersTokens;
use App\BalanceLog;
use App\ExchangeRate;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Classes\Functions;
use App\Package;
use App\User;
use App\YigimPaymentSystemLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class YigimController extends ApiController
{
    public function login() {
        try {
            $username = \Illuminate\Support\Facades\Request::header('username');
            $password = \Illuminate\Support\Facades\Request::header('password');

            $functions = new Functions();
            if ($functions->validate_for_api([$username, $password])) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'validate',
                    'message' => 'Username and password are required!'
                ],  Response::HTTP_NOT_FOUND);
            }

            $user = ApiUser::where(['username'=>$username, 'password'=>$password, 'role'=>'yigim'])->select('id')->first();

            if (!$user) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'Not found',
                    'message' => 'User not found or wrong password. Check that you entered your information correctly.'
                ],  Response::HTTP_NOT_FOUND);
            }

            $user_id = $user->id;

            $token = Str::random(200) . $user_id . time();
            $create_token = ApiUsersTokens::create(['token'=>$token, 'last_active_time'=>time(), 'user_id' => $user_id]);
            if ($create_token) {
                $_code = Response::HTTP_OK;
                $responseMessage = "Success! Your token is ready...";
            }
            else {
                return response([
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'type' => 'Token error',
                    'message' => 'Sorry, An error occurred...',
                    'token' => $token
                ],  Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response([
                'status' => $_code,
                'type' => 'Success',
                'message' => $responseMessage,
                'token' => $token
            ], $_code);
        } catch (\Exception $e) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_client_details(Request $request) {
        $validator = Validator::make($request->all(), [
            'client' => ['required', 'string', 'max:7'],
        ]);
        if ($validator->fails()) {
            return response(['status' => Response::HTTP_BAD_REQUEST, 'type' => 'validation', 'message' => $validator->errors()->toArray()],Response::HTTP_BAD_REQUEST);
        }
        try {
            $client_code = $request->client;
            if (strlen($client_code) != 7 || strtoupper($client_code[0]) != 'AS' || !is_numeric(substr($client_code, 1))) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'Wrong format',
                    'message' => 'Client ID is wrong format.',
                ],  Response::HTTP_NOT_FOUND);
            }

            $client_code = substr($client_code, 1);

            $suite = (int) $client_code;

            $client = User::where(['id'=>$suite, 'role_id'=>2])
                ->whereNull('deleted_by')
                ->select('name', 'surname', 'balance')
                ->first();

            if (!$client) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'Not found',
                    'message' => 'Client not found.',
                ],  Response::HTTP_NOT_FOUND);
            }

            $date = Carbon::today();
            $rates = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
                ->where('to_currency_id', 3) // to AZN
                ->select('rate', 'from_currency_id', 'to_currency_id')
                ->orderBy('id', 'desc')
                ->get();
            if (!$rates) {
                // rate note found
                return response([
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'type' => 'Error',
                    'message' => 'Sorry, An error occurred...'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $rate_usd_to_azn = $this->calculate_exchange_rate($rates, 1);
            $balance = $client->balance;
            $balance_azn = $balance * $rate_usd_to_azn;
            $balance_azn = (float) sprintf('%0.2f', $balance_azn);

            $total_debt = 0; // azn

            $packages = Package::where(['client_id'=>$suite, 'paid_status'=>0])
                ->whereNull('deleted_by')
                ->select('total_charge_value', 'paid', 'currency_id')
                ->get();

            if (count($packages) > 0) {
                foreach ($packages as $package) {
                    $real_debt = $package->total_charge_value - $package->paid;
                    $real_currency = $package->currency_id;

                    $rate_to_azn = $this->calculate_exchange_rate($rates, $real_currency);
                    $debt_azn = $real_debt * $rate_to_azn;
                    $debt_azn = sprintf('%0.2f', $debt_azn);

                    $total_debt += $debt_azn;
                }
            }

            $total_debt = (float) sprintf('%0.2f', $total_debt);

            return response([
                'status' => Response::HTTP_OK,
                'type' => 'Success',
                'client' => 'AS' . $suite,
                'full_name' => $client->name . ' ' . $client->surname,
                'balance' => $balance_azn,
                'debt' => $total_debt,
                'currency' => 'AZN'
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function pay_balance(Request $request) {
        $validator = Validator::make($request->all(), [
            'client' => ['required', 'string', 'max:7'],
            'time' => ['required', 'integer'],
            'amount' => ['required'],
            'receipt' => ['required', 'string', 'max:255'],
            'platform' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response(['status' => Response::HTTP_BAD_REQUEST, 'type' => 'validation', 'message' => $validator->errors()->toArray()],Response::HTTP_BAD_REQUEST);
        }
        try {
            $client_code = $request->client;
            if (strlen($client_code) != 7 || strtoupper($client_code[0]) != 'AS' || !is_numeric(substr($client_code, 1))) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'Wrong format',
                    'message' => 'Client ID is wrong format.',
                ],  Response::HTTP_NOT_FOUND);
            }

            $client_code = substr($client_code, 1);

            $suite = (int) $client_code;

            $amount = $request->amount; // azn
            $receipt_no = $request->receipt;
            $platform = $request->platform;
            $time = $request->time;

            $client = User::where(['id'=>$suite, 'role_id'=>2])
                ->whereNull('deleted_by')
                ->select('balance')
                ->first();

            if (!$client) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'Not found',
                    'message' => 'Client not found.',
                ],  Response::HTTP_NOT_FOUND);
            }

            $date = Carbon::today();
            $rate = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
                ->where('from_currency_id', 1)
                ->where('to_currency_id', 3)
                ->select('rate')
                ->orderBy('id', 'desc')
                ->first();

            if (!$rate) {
                // rate note found
                return response([
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'type' => 'Error',
                    'message' => 'Sorry, An error occurred...'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $rate_usd_azn = $rate->rate;
            $amount_usd = $amount / $rate_usd_azn;
            $amount_usd = sprintf('%0.2f', $amount_usd);

            $log = YigimPaymentSystemLog::create([
                'client_id' => $suite,
                'receipt_no' => $receipt_no,
                'amount' => $amount,
                'amount_usd' => $amount_usd,
                'platform' => $platform,
                'time' => $time,
                'status' => 0
            ]);

            $old_balance_usd = $client->balance;
            $new_balance_usd = $old_balance_usd + $amount_usd;

            $payment_code = Str::random(20);
            BalanceLog::create([
                'payment_code' => $payment_code,
                'amount' => $amount_usd,
                'amount_azn' => $amount,
                'client_id' => $suite,
                'status' => 'in',
                'type' => 'yigim',
                'platform' => $platform,
                'created_by' => $suite
            ]);

            User::where('id', $suite)->update(['balance' => $new_balance_usd]);

            YigimPaymentSystemLog::where('id', $log->id)->update(['status' => 1]);

            return response([
                'status' => Response::HTTP_OK,
                'type' => 'Success',
                'client' => 'AS' . $suite,
                'payment_code' => $payment_code,
                'receipt' => $receipt_no,
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function pay_control(Request $request) {
        $validator = Validator::make($request->all(), [
            'receipt' => ['required', 'string', 'max:255']
        ]);
        if ($validator->fails()) {
            return response(['status' => Response::HTTP_BAD_REQUEST, 'type' => 'validation', 'message' => $validator->errors()->toArray()],Response::HTTP_BAD_REQUEST);
        }
        try {
            $receipt_no = $request->receipt;

            $log = YigimPaymentSystemLog::where('receipt_no', $receipt_no)
                ->select('id', 'client_id', 'amount', 'status', 'created_at')
                ->orderBy('id', 'desc')
                ->first();

            if (!$log) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'Not found',
                    'message' => 'Payment not found.',
                ],  Response::HTTP_NOT_FOUND);
            }

            $suite = (int) $log->client_id;
            $amount = (float) $log->amount;
            $status = $log->status;
            $date = $log->created_at;

            if ($status == 1) {
                $payment_type = 'success';
            } else {
                $payment_type = 'failed';
            }

            YigimPaymentSystemLog::where('id', $log->id)->update([
                'checked' => 1,
                'checked_at' => Carbon::now()
            ]);

            return response([
                'status' => Response::HTTP_OK,
                'type' => 'Success',
                'payment_type' => $payment_type,
                'client' => 'AS' . $suite,
                'amount' => $amount,
                'currency' => 'AZN',
                'date' => $date
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function calculate_exchange_rate($rates, $from)
    {
        try {
            foreach ($rates as $rate) {
                if ($rate->from_currency_id == $from) {
                    return $rate->rate;
                }
            }

            return 0;
        } catch (\Exception $exception) {
            return 0;
        }
    }
}
