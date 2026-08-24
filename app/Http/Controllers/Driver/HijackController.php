<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\HijackAlert;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HijackController extends Controller
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
            HijackAlert::create([
                'driver_id' => $driver->id,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'status' => 'active',
                'triggered_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'Hijack Alert',
                'module' => 'Emergency',
                'description' => 'Vehicle hijack alert triggered',
                'ip_address' => request()->ip(),
            ]);

            Notification::create([
                'title' => 'Vehicle Hijack Alert',
                'message' => 'Possible vehicle hijacking detected.',
                'type' => 'hijack',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hijack alert sent.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send hijack alert'
            ], 500);
        }
    }
}
