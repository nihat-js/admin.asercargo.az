<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Classes\Functions;
use App\Package;
use App\PackageStatus;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;

class DeliveryController extends ApiController
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
                ->where('package.payment_receipt', $receipt)
                ->where('package.paid_status', 1)
                ->whereNull('package.delivered_by')
                ->whereNull('package.deleted_by')
                ->select(
                    'number',
                    'internal_id',
                    'total_charge_value as amount',
                    'paid',
                    'cur.name as currency',
                    'client_id'
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

    public function set_delivered() {
        try {
            $receipt = Input::get("receipt");
            $package_no = Input::get("package");
            $user_id = \Request::get('user_id');
            $user_location_id = \Request::get('user_location_id'); //only your location

            $functions = new Functions();
            if ($functions->validate_for_api([$receipt, $package_no])) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'validate',
                    'message' => 'Fill all inputs!'
                ],  Response::HTTP_NOT_FOUND);
            }

            $package = Package::where('payment_receipt', $receipt)
                ->where('number', $package_no)
                ->whereRaw("number = '" . $package_no . "' or internal_id = '" . $package_no . "'")
                ->where('package.paid_status', 1)
                ->whereNull('package.delivered_by')
                ->whereNull('package.deleted_by')
                ->orderBy('id', 'desc')
                ->select('id', 'number')
                ->first();

            if (!$package) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'warning',
                    'message' => 'Package not found!'
                ],  Response::HTTP_NOT_FOUND);
            }

            $package_id = $package->id;

            Package::where('id', $package_id)->update(['delivered_by'=>$user_id, 'delivered_at'=>Carbon::now()]);

            PackageStatus::create([
                'package_id' => $package_id,
                'status_id' => 3, //delivered
                'created_by' => $user_id
            ]);

            return response([
                'status' => Response::HTTP_OK,
                'package' => $package->number,
                'delivered' => 1
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
