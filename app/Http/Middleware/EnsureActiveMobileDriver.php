<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveMobileDriver
{
    public function handle(Request $request, Closure $next): Response
    {
        $driver = $request->user()?->driver;

        if (
            !$driver
            || $driver->archived_at !== null
            || $driver->management_status !== 'active'
            || $request->user()->status !== 'approved'
        ) {
            return response()->json([
                'message' => 'The driver account is inactive.',
            ], 403);
        }

        return $next($request);
    }
}
