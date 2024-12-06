<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Classes\Functions;
use App\Package;
use App\Position;
use App\TrackingLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;

class DistributorController extends ApiController
{
    public function change_position(Request $request) {
        try {
            $user_id = \Request::get('user_id');
            $user_location_id = \Request::get('user_location_id');

            $position_no = Input::get("position");
            $package_number = Input::get("package");

            $functions = new Functions();
            if ($functions->validate_for_api([$position_no, $package_number])) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'validate',
                    'message' => 'Fill all inputs!'
                ],  Response::HTTP_NOT_FOUND);
            }

            $package = Package::whereRaw("number = '" . $package_number . "' or internal_id = '" . $package_number . "'")
//                ->where('number', $package_number)
                ->whereNull('deleted_by')
                ->whereNull('delivered_by')
                ->orderBy('id', 'desc')
                ->select('id')
                ->first();

            if (!$package) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'warning',
                    'message' => 'Package is not found!'
                ],  Response::HTTP_NOT_FOUND);
            }

            $package_id = $package->id;

            $position = Position::where('name', $position_no)->whereNull('deleted_by')
                ->where('location_id', $user_location_id)
                ->orderBy('id', 'desc')
                ->select('id')
                ->first();

            if (!$position) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'warning',
                    'message' => 'Position is not found at your location!'
                ],  Response::HTTP_NOT_FOUND);
            }

            $position_id = $position->id;

            TrackingLog::create([
                'package_id' => $package_id,
                'operator_id' => $user_id,
                'position_id' => $position_id,
                'created_by' => $user_id
            ]);

            return response([
                'status' => Response::HTTP_OK,
                'type' => 'success',
                'message' => 'Position is changed!'
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
