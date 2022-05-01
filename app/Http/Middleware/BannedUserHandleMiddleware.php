<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BannedUserHandleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->user()->is_active == false) {
            auth()->logout();
            return response()->json(['message' => 'Your account is banned'], 403);
        }
        return $next($request);
    }
}
