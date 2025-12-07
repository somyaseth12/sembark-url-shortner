<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    { 
        // If not authenticated, redirect to login
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // If authenticated but role doesn't match, redirect to login
        if (! in_array($request->user()->role->name, $roles)) {
            return redirect()->route('login')->with('error', 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
