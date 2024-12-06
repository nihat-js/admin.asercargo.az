<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Classes\Functions;
use App\Package;
use App\PackageStatus;
use App\PaymentLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;

class CashierController extends Controller
{
    public function get_packages() {
        try {
            $receipt = Input::get("receipt");
            $user_location_id = \Request::get('user_location_id'); //only your location

            $functions = new Functions();
            if ($functions->validate_for_api([$receipt])) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'validate',
                    'message' => 'Fill all inputs!'
                ],  Response::HTTP_NOT_FOUND);
            }

            $packages = Package::leftJoin('currency as cur', 'package.currency_id', '=', 'cur.id')
                ->leftJoin('users as u', 'package.client_id', '=', 'u.id')
                ->where('package.payment_receipt', $receipt)
                ->whereNull('package.delivered_by')
                ->whereNull('package.deleted_by')
                ->select(
                    'number',
                    'internal_id',
                    'total_charge_value as amount',
                    'paid',
                    'paid_status',
                    'cur.name as currency',
                    'client_id',
                    'u.passport_number as client_passport',
                    'u.balance'
                )
                ->get();

            return response([
                'status' => Response::HTTP_OK,
                'packages' => $packages
            ],  Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function set_to_paid() {
        try {
            $pay = Input::get("pay");
            $package_no = Input::get("package");
            $client_id = Input::get("client");
            $payment_type = Input::get("type");
            $user_id = \Request::get('user_id');

            $paid_status = 0;
            $residue = 0;

            $functions = new Functions();
            if ($functions->validate_for_api([$pay, $package_no, $client_id, $payment_type])) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'validate',
                    'message' => 'Fill all inputs!'
                ],  Response::HTTP_NOT_FOUND);
            }

            $package = Package::leftJoin('position as p', 'package.position_id', '=', 'p.id')
                ->where('package.number', $package_no)
                ->whereNull('package.deleted_by')
                ->whereNull('package.delivered_by')
                ->where('package.paid_status', 0)
                ->select(
                    'package.id',
                    'package.total_charge_value as amount',
                    'package.paid',
                    'package.currency_id',
                    'p.name as position'
                )
                ->orderBy('id', 'desc')
                ->first();

            if (!$package) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'warning',
                    'message' => 'Package not found!'
                ],  Response::HTTP_NOT_FOUND);
            }

            $package_id = $package->id;
            $amount = $package->amount;
            $paid = $package->paid;
            $currency_id = $package->currency_id;
            $position = $package->position;

            if ($position == null) {
                $position = '---';
            }

            PaymentLog::create([
                'payment' => $pay,
                'currency_id' => $currency_id,
                'client_id' => $client_id,
                'package_id' => $package_id,
                'type' => $payment_type,
                'created_by' => $user_id
            ]);

            $total_paid = $paid + $pay;

            if ($total_paid >= $amount) {
                $paid_status = 1;
                $residue = 0;
            } else {
                $paid_status = 0;
                $residue = $amount - $total_paid;
            }

            Package::where('id', $package_id)
                ->update([
                    'paid' => $total_paid,
                    'paid_status' => $paid_status
                ]);

            if ($paid_status == 1) {
                PackageStatus::create([
                    'package_id' => $package_id,
                    'status_id' => 2, //paid
                    'created_by' => $user_id
                ]);
            }

            return response([
                'status' => Response::HTTP_OK,
                'package' => $package_no,
                'amount' => $amount,
                'paid' => $total_paid,
                'paid_status' => $paid_status,
                'residue' => $residue,
                'position' => $position
            ],  Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
