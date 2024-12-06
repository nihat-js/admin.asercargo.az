<?php

namespace App\Http\Controllers\Api;

use App\ApiUser;
use App\ApiUsersTokens;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Classes\Functions;
use App\PhonesApiLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PhonesController extends ApiController
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

            $user = ApiUser::where(['username'=>$username, 'password'=>$password, 'role'=>'colibri_it'])->select('id')->first();

            if (!$user) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'Not found',
                    'message' => 'User not found or wrong password. Check that you entered your information correctly.'
                ],  Response::HTTP_NOT_FOUND);
            }

            $user_id = $user->id;

            $token = Str::random(200) . $user_id . time();
            ApiUsersTokens::where(['user_id' => $user_id])->update(['deleted_at' => Carbon::now()]); // for only one session
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
            'phone' => ['required', 'string', 'max:30'],
        ]);
        if ($validator->fails()) {
            return response(['status' => Response::HTTP_BAD_REQUEST, 'type' => 'validation', 'message' => $validator->errors()->toArray()],Response::HTTP_BAD_REQUEST);
        }
        try {
            $user_id = \Request::get('user_id');

            $client = User::where(['role_id'=>2])
                ->whereRaw('(phone1 = ? or phone2 = ?)', [$request->phone, $request->phone])
                ->whereNull('deleted_by')
                ->select('id as suite', 'name', 'surname')
                ->first();

            if (!$client) {
                PhonesApiLog::create([
                    'phone' => $request->phone,
                    'client_id' => 0,
                    'user_id' => $user_id
                ]);

                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'Not found',
                    'message' => 'Client not found!'
                ],  Response::HTTP_NOT_FOUND);
            }

            PhonesApiLog::create([
                'phone' => $request->phone,
                'client_id' => $client->suite,
                'user_id' => $user_id
            ]);

            return response([
                'status' => Response::HTTP_OK,
                'type' => 'Success',
                'suite' => $client->suite,
                'client' => $client->name . ' ' . $client->surname
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // this is disable
//    public function get_clients(Request $request) {
//        try {
//            $clients = User::where(['role_id'=>2])
//                ->whereNull('deleted_by')
//                ->select('id as suite', 'name', 'surname', 'phone1', 'phone2')
//                ->get();
//
//            return response([
//                'status' => Response::HTTP_OK,
//                'type' => 'Success',
//                'clients' => $clients
//            ], Response::HTTP_OK);
//        } catch (\Exception $exception) {
//            return response([
//                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
//                'type' => 'Error',
//                'message' => 'Sorry, An error occurred...'
//            ], Response::HTTP_INTERNAL_SERVER_ERROR);
//        }
//    }
}
