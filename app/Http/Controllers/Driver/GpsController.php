<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\GpsLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GpsController extends Controller
{
    /**
     * Update driver's current GPS location.
     *
     * A new GPS record is created every time.
     * This preserves GPS history for monitoring and reports.
     */
    public function update(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Driver profile
        |--------------------------------------------------------------------------
        */

        $driver = $user->driver;

        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver profile not found.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate coordinates
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make(
            $request->all(),
            [
                'latitude' => [
                    'required',
                    'numeric',
                    'between:-90,90',
                ],

                'longitude' => [
                    'required',
                    'numeric',
                    'between:-180,180',
                ],

                'accuracy' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'speed_kmh' => ['nullable', 'numeric', 'min:0', 'max:500'],
                'speed_limit_kmh' => ['nullable', 'numeric', 'gt:0', 'max:300'],
                'road_type' => ['nullable', 'string', 'max:50'],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid GPS coordinates.',
                'errors' => $validator->errors(),
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Get validated coordinates
        |--------------------------------------------------------------------------
        */

        $validated = $validator->validated();

        $latitude = (float) $validated['latitude'];
        $longitude = (float) $validated['longitude'];
        $speedKmh = array_key_exists('speed_kmh', $validated) && $validated['speed_kmh'] !== null
            ? (float) $validated['speed_kmh']
            : null;
        $speedLimitKmh = $this->resolveSpeedLimit($validated);
        $speedStatus = $this->speedStatus($speedKmh, $speedLimitKmh);

        /*
        |--------------------------------------------------------------------------
        | Save GPS history
        |--------------------------------------------------------------------------
        */

        $gpsLocation = GpsLocation::create([
            'driver_id' => $driver->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'recorded_at' => now(),
            'speed_kmh' => $speedKmh,
            'speed_status' => $speedStatus,
            'speed_limit_kmh' => $speedLimitKmh,

            'speed_limit_label' => $speedLimitKmh === null ? 'Speed limit unavailable' : $speedLimitKmh . ' km/h',
        ]);

        $activeDispatch = Dispatch::with('vehicle')
            ->where('driver_id', $driver->id)
            ->whereNotIn('status', [
                Dispatch::STATUS_COMPLETED,
                Dispatch::STATUS_CLOSED,
                Dispatch::STATUS_CANCELLED,
            ])
            ->latest('assigned_at')
            ->first();

        $activeDispatch?->vehicle?->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'gps_id' =>
            $gpsLocation->id,

            'driver_id' =>
            $driver->id,

            'latitude' =>
            $latitude,

            'longitude' =>
            $longitude,

            'speed_kmh' => $speedKmh,

            'speed_status' => $speedStatus,

            'speed_limit_kmh' => $speedLimitKmh,

            'recorded_at' =>
            $gpsLocation->recorded_at?->toISOString(),
        ]);
    }

    private function resolveSpeedLimit(array $validated): ?float
    {
        if (isset($validated['speed_limit_kmh'])) return (float) $validated['speed_limit_kmh'];
        $roadType = $validated['road_type'] ?? null;
        $limit = $roadType ? config("services.muniresq.speed.road_limits_kmh.{$roadType}") : null;
        return is_numeric($limit) && (float) $limit > 0 ? (float) $limit : null;
    }

    private function speedStatus(?float $speedKmh, ?float $speedLimitKmh): ?string
    {
        if ($speedKmh === null) return null;
        if ($speedLimitKmh === null) return 'unrated';
        $yellow = 1 + (config('services.muniresq.speed.yellow_over_limit_percent', 10) / 100);
        $red = 1 + (config('services.muniresq.speed.red_over_limit_percent', 20) / 100);
        return $speedKmh > $speedLimitKmh * $red ? 'red' : ($speedKmh > $speedLimitKmh * $yellow ? 'yellow' : 'green');
    }
}
