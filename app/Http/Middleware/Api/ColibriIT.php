<?php

namespace App\Http\Middleware\Api;

use App\ApiUser;
use App\ApiUsersTokens;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;

class ColibriIT
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

            $user = ApiUsersTokens::leftJoin('api_users as user', 'api_users_tokens.user_id', '=', 'user.id')
                ->where(['api_users_tokens.token'=>$token, 'user.role'=>'colibri_it'])
                ->where('api_users_tokens.last_active_time', '>', $current_time-4*60*60)
                ->whereNull('api_users_tokens.deleted_at')
                ->whereNull('user.deleted_by')
                ->select('user.id')
                ->orderBy('id', 'desc')
                ->first();

            if (!$user) {
                return response([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'Because, you have been inactive for more than 4 hours, your token have timed out. Please, request a new token.',
                ],  Response::HTTP_FORBIDDEN);
            }

            $user_id = $user->id;
            $request->attributes->add(['user_id' => $user_id]);

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
