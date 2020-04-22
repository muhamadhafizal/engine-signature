<?php

namespace App\Http\Middleware;

use Closure;
use App\User; 
use Illuminate\Support\Facades\Auth; 
class TokenMiddleware
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
        $token = Auth::user()->token();
        $expires_date = $token->expires_at;
        $date = date('Y-m-d H:i:s');

        if ($expires_date < $date){
            return response()->json(['error'=>'token expired'], 402);
        } else {
            return $next($request);
        }
    }
}


