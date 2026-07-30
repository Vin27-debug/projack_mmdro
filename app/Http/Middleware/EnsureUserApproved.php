<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect('/login');
        }

        if ($user->status !== 'approved') {

            auth()->logout();

            return redirect('/login')
                ->with('error', 'Your account is pending approval.');
        }

        return $next($request);
    }
}
