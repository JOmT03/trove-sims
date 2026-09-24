<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['Owner', 'Manager'])) {
            abort(403, 'Unauthorized. Owner or Manager access required.');
        }

        return $next($request);
    }
}