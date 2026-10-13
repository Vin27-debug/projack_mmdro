<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminIncidentWriteAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user
                && $user->hasRole('admin')
                && ! $user->hasAnyRole(['super-admin', 'superadmin']),
            403,
            'Only Admin users may manage incidents.'
        );

        return $next($request);
    }
}
