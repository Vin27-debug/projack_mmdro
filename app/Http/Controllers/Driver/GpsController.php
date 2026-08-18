<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\GpsLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GpsController extends Controller
{
    /**
     * Update driver's current GPS location.
     *
     * GPS tracking is independent from dispatch status.
     * This means the driver's latest location can still be
     * monitored even after a mission has been completed.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $driver = $user->driver;

        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver profile not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'latitude' => [
                'required',
                'numeric',
                'between:-90,90'
            ],

            'longitude' => [
                'required',
                'numeric',
                'between:-180,180'
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid GPS coordinates.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $latitude = (float) $validated['latitude'];
        $longitude = (float) $validated['longitude'];

        /*
        |--------------------------------------------------------------------------
        | Save GPS history
        |--------------------------------------------------------------------------
        |
        | We intentionally create a new record instead of updating the old
        | record. This gives us a complete GPS history for reports and
        | monitoring.
        |
        */

        $gpsLocation = GpsLocation::create([
            'driver_id' => $driver->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'recorded_at' => now(),
        ]);

        return response()->json([
            'success' => true,

            'gps_id' => $gpsLocation->id,

            'driver_id' => $driver->id,

            'latitude' => $latitude,

            'longitude' => $longitude,

            'recorded_at' =>
            $gpsLocation->recorded_at?->toISOString(),
        ]);
    }
}
