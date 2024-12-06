<?php

namespace App\Http\Middleware\Api;

use App\User;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;

class Operator
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
            $current_time = time();
            $token = Request::header('token');
            $role = 7;

            $user = User::where('token', '=', $token)->where('last_active_time', '>', $current_time-4*60*60)->whereNull('deleted_by')->select('id', 'role_id', 'destination_id as location_id')->first();

            if (!$user) {
                return response([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'Because, you have been inactive for more than 4 hours, your token have timed out. Please, request a new token.',
                ],  Response::HTTP_FORBIDDEN);
            }

            $user_role = $user->role_id;

            if ($user_role != $role) {
                return response([
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Access denied!',
                ],  Response::HTTP_UNAUTHORIZED);
            }

            $user_id = $user->id;
            $location_id = $user->location_id;
            $request->attributes->add(['user_id' => $user_id]);
            $request->attributes->add(['user_location_id' => $location_id]);

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
