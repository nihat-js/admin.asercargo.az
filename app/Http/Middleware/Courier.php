<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Courier
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
        if (Auth::user()->role() == 8 or Auth::user()->role() == 1 or Auth::user()->role() == 9) {
            return $next($request);
        }
        return redirect()->route("access_denied");
    }
}
