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

            'recorded_at' =>
            $gpsLocation->recorded_at?->toISOString(),
        ]);
    }
}
