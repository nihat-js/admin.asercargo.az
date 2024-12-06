<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Classes\Functions;
use App\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class LoginController extends ApiController
{
    public function login() {
        try {
            $username = Request::header('username');
            $password = Request::header('password');
            $token = "";
            $role_id = 0;

            $functions = new Functions();
            if ($functions->validate_for_api([$username, $password])) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'type' => 'validate',
                    'message' => 'Fill all inputs!'
                ],  Response::HTTP_NOT_FOUND);
            }

            $user = User::leftJoin('locations as l', 'users.destination_id', '=', 'l.id')
                ->where(['users.username'=>$username])
                ->whereNull('users.deleted_by')
                ->select('users.id', 'users.password', 'users.role_id', 'l.name as location', 'users.destination_id as location_id', 'users.name', 'users.surname')
                ->first();

            if (!$user) {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'User not found. Check that you entered your information correctly.',
                    'token' => $token
                ],  Response::HTTP_NOT_FOUND);
            }

            $user_password = $user->password;
            $user_id = $user->id;
            $location = $user->location;
            $location_id = $user->location_id;
            $user_full_name = $user->name . ' ' . $user->surname;

            if (Hash::check($password, $user_password)) {
                $token = Str::random(255);
                $create_token = User::where('id', $user_id)->update(['token'=>$token, 'last_active_time'=>time()]);
                if ($create_token) {
                    $_code = Response::HTTP_OK;
                    $responseMessage = "Success! Your token is ready...";
                    $role_id = $user->role_id;
                }
                else {
                    return response([
                        'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                        'message' => 'Sorry, An error occurred...',
                        'token' => $token
                    ],  Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
            else {
                return response([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'User not found. Check that you entered your information correctly.',
                    'token' => $token
                ],  Response::HTTP_NOT_FOUND);
            }

            return response([
                'status' => $_code,
                'message' => $responseMessage,
                'token' => $token,
                'role' => $role_id,
                'location' => $location,
                'location_id' => $location_id,
                'user_id' => $user_id,
                'full_name' => $user_full_name
            ], $_code);
        } catch (\Exception $e) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
