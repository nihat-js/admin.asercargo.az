<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;

class Azerpost
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

            $token = Request::header('x-api-key');
            $checkToken = 'QbYrn6tumMzkiYuoH8hv6wuQ';

            if($token != $checkToken){

                return response()->json([
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'AUTHORIZATION_KEY_NOT_DEFINED'
                ], Response::HTTP_INTERNAL_SERVER_ERROR);

            }

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
