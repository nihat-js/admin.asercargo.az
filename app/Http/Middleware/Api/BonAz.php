<?php

namespace App\Http\Middleware\Api;

use App\ApiUser;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;

class BonAz
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            //$current_time = time();
            $username = Request::header('username');
            $password = Request::header('password');

            $user = ApiUser::where(['username'=>$username, 'password'=>$password])->select('id', 'role')->first();

            if (!$user) {
                return response([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'User not found!',
                ],  Response::HTTP_FORBIDDEN);
            }

            if ($user->role != 'bon_az') {
                return response([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'Access denied!',
                ],  Response::HTTP_FORBIDDEN);
            }

            //$user_id = $user->id;
            //$request->attributes->add(['user_id' => $user_id]);

            return $next($request);
        } catch (\Exception $e) {
            return response([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'type' => 'Error',
                'message' => 'Sorry, An error occurred...'
            ],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
