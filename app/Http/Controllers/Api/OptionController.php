<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Classes\Functions;
use App\Option;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;

class OptionController extends ApiController
{
    public function get_device_address() {
        try {
            $device1 = Input::get("device");

            $functions = new Functions();
            if ($functions->validate_for_api([$device1])) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'validate',
                    'message' => 'Fill all inputs!'
                ],  Response::HTTP_NOT_FOUND);
            }

            $device2 = Option::where('device1', $device1)->whereNull('deleted_by')
                ->select('device2 as device', 'location_id as location')
                ->orderBy('id', 'desc')
                ->first();

            if (!$device2) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'warning',
                    'message' => 'Device not found!'
                ],  Response::HTTP_NOT_FOUND);
            }

            return response([
                'status' => Response::HTTP_OK,
                'device' => $device2->device,
                'location' => $device2->location
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
