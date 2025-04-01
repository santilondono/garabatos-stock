<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

     public function handle($request, Closure $next, ...$roles)
     {
         if (Auth::check() && in_array(Auth::user()->role_id, $roles)) {
             return $next($request);
         }
 
         abort(403, 'Access denied.');
     }
}
