<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class MobileAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'driver_id' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $driverId = Str::lower(trim($validated['driver_id']));
        $rateLimitKey = 'mobile-driver-login:' . hash('sha256', $driverId . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return response()->json([
                'message' => 'Too many login attempts. Try again later.',
            ], 429)->header('Retry-After', (string) RateLimiter::availableIn($rateLimitKey));
        }

        $driver = Driver::activeAccount()
            ->whereRaw('LOWER(badge_id) = ?', [$driverId])
            ->with('user')
            ->first();

        if (
            !$driver
            || !$driver->user
            || !$driver->user->hasRole('driver')
            || !Hash::check($validated['password'], $driver->user->password)
        ) {
            RateLimiter::hit($rateLimitKey, 60);

            return response()->json([
                'message' => 'The provided driver credentials are invalid or inactive.',
            ], 422);
        }

        RateLimiter::clear($rateLimitKey);

        $token = $driver->user
            ->createToken('muniresq-driver-app', ['driver:gps'])
            ->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'driver' => [
                'id' => $driver->id,
                'badge_id' => $driver->badge_id,
                'name' => $driver->user->name,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['message' => 'Logged out.']);
    }
}
