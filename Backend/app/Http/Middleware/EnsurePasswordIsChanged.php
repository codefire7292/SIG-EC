<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && 
            $request->user()->must_change_password && 
            !$request->routeIs('password.change') && 
            !$request->routeIs('password.change.update') && 
            !$request->routeIs('logout') &&
            !$request->is('logout')) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
