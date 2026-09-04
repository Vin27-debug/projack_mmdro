<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PanicAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanicController extends Controller
{
    public function trigger(Request $request)
    {
        $driver = Auth::user()?->driver;

        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Validate coordinates
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ], [
            'latitude.required' => 'Latitude is required',
            'latitude.numeric' => 'Latitude must be numeric',
            'latitude.between' => 'Latitude must be between -90 and 90',
            'longitude.required' => 'Longitude is required',
            'longitude.numeric' => 'Longitude must be numeric',
            'longitude.between' => 'Longitude must be between -180 and 180',
        ]);

        try {
            PanicAlert::create([
                'driver_id' => $driver->id,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'resolved' => false,
                'triggered_at' => now(),
            ]);

            Notification::create([
                'title' => 'Panic Alert',
                'message' => 'A driver triggered the panic button.',
                'type' => 'panic'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Panic alert sent.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send panic alert'
            ], 500);
        }
    }
}
