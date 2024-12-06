<?php

namespace App\Http\Controllers\Api;

use App\Batches;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Classes\Collector;
use App\Http\Controllers\Classes\Functions;
use App\Item;
use App\Package;
use App\PackageStatus;
use App\Position;
use App\TrackingLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;

class CollectorController extends ApiController
{
    public function check_package()
    {
        try {
            $package = Input::get("package");
            $user_location_id = \Request::get('user_location_id');

            $functions = new Functions();
            if ($functions->validate_for_api([$package])) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'validate',
                    'message' => 'Fill all inputs!'
                ], Response::HTTP_NOT_FOUND);
            }

            $request = new Request();
            $request->merge(['number' => $package]);

            $collector = new Collector();
            $data = $collector->check_package($request, $user_location_id);

            return response([
                'status' => Response::HTTP_OK,
                'data' => $data
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function client_control()
    {
        try {
            $client_id = Input::get("client");

            $functions = new Functions();
            if ($functions->validate_for_api([$client_id])) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'validate',
                    'message' => 'Fill all inputs!'
                ], Response::HTTP_NOT_FOUND);
            }

            $collector = new Collector();
            $check_client = $collector->client_control($client_id);

            if ($check_client != false) {
                //from object to boolen
                $check_client = true;
            }

            return response([
                'status' => Response::HTTP_OK,
                'check_client' => $check_client
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_default_category_for_seller()
    {
        try {
            $seller = Input::get("seller");

            $functions = new Functions();
            if ($functions->validate_for_api([$seller])) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'validate',
                    'message' => 'Fill all inputs!'
                ], Response::HTTP_NOT_FOUND);
            }

            $collector = new Collector();
            $category = $collector->get_default_category_for_seller($seller);

            return response([
                'status' => Response::HTTP_OK,
                'category' => $category
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function save_package(Request $request)
    {
        try {
            $user_id = \Request::get('user_id');
            $user_location_id = \Request::get('user_location_id');

            $collector = new \App\Http\Controllers\CollectorController();

            $save = $collector->add_collector($request, true, $user_id, $user_location_id);

            return response([
                'status' => Response::HTTP_OK,
                'data' => $save
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create_batch()
    {
        try {
            $user_id = \Request::get('user_id');
            $user_location_id = \Request::get('user_location_id');

            $batch = Input::get("batch"); //batch name

            $functions = new Functions();
            if ($functions->validate_for_api([$batch])) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'validate',
                    'message' => 'Fill all inputs!'
                ], Response::HTTP_NOT_FOUND);
            }

            $batch_control = Batches::where('name', $batch)->whereNull('deleted_by')
                ->select('id', 'count')
                ->first();

            $batch_id = 0;
            $count = 0;

            if ($batch_control) {
                $batch_id = $batch_control->id;
                $count = $batch_control->count;
            } else {
                $add = Batches::create([
                    'name' => $batch,
                    'count' => 0,
                    'location_id' => $user_location_id,
                    'created_by' => $user_id
                ]);

                $batch_id = $add->id;
            }

            return response([
                'status' => Response::HTTP_OK,
                'batch_id' => $batch_id,
                'count' => $count
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function receive_package(Request $request)
    {
        try {
            $user_id = \Request::get('user_id');
            $user_location_id = \Request::get('user_location_id');
            $package = Input::get("package");
            $batch = Input::get("batch"); //batch_id

            $functions = new Functions();
            if ($functions->validate_for_api([$package, $batch])) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'validate',
                    'message' => 'Fill all inputs!'
                ], Response::HTTP_NOT_FOUND);
            }

            $batch_control = Batches::where('id', $batch)->whereNull('deleted_by')
                ->select('id', 'count')
                ->first();

            if (!$batch_control) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'warning',
                    'message' => 'Batch not found!'
                ], Response::HTTP_NOT_FOUND);
            }

            $old_count = $batch_control->count;

            $package_exist = Package::where('number', $package)
                ->whereNull('deleted_by')
                ->select('id', 'received_by')
                ->first();

            if ($package_exist) {
                //update
                if ($package_exist->received_by == null) {
                    Package::where('id', $package_exist->id)
                        ->update([
                            'batch_id' => $batch,
                            'received_by' => $user_id,
                            'received_at' => Carbon::now(),
                            'departure_id' => $user_location_id
                        ]);

                    PackageStatus::create([
                        'status_id' => 4, //received
                        'package_id' => $package_exist->id,
                        'created_by' => $user_id
                    ]);
                } else {
                    return response([
                        'status' => Response::HTTP_CONFLICT,
                        'type' => 'warning',
                        'message' => 'Package is already exist!'
                    ], Response::HTTP_CONFLICT);
                }
            } else {
                //create
                $add = Package::create([
                    'number' => $package,
                    'batch_id' => $batch,
                    'created_by' => $user_id,
                    'received_by' => $user_id,
                    'received_at' => Carbon::now(),
                    'departure_id' => $user_location_id
                ]);

                Item::create([
                    'package_id' => $add->id,
                    'created_by' => $user_id
                ]);

                PackageStatus::create([
                    'status_id' => 4, //received
                    'package_id' => $add->id,
                    'created_by' => $user_id
                ]);
            }

            $new_count = $old_count + 1;
            Batches::where('id', $batch)->update(['count'=>$new_count]);

            return response([
                'status' => Response::HTTP_OK,
                'type' => 'success',
                'message' => 'Package is received!',
                'count' => $new_count
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'error',
                'message' => 'Sorry, An error occurred...'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
