<?php

namespace App\Http\Middleware\Api;

use App\User;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;

class AserCollector
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

            $token = Request::header('token');
            $collector = User::where('token', $token)->first();
            if($token == null || $token == "" || !$collector){

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
