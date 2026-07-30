<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Models\GpsLocation;
use App\Models\VehicleDriverAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GpsController extends Controller
{
    public function update(Request $request)
    {
        $driver = Auth::user()?->driver;

        if (!$driver) {
            return response()->json([
                'success' => false,
                'message' => 'Driver not found'
            ], 404);
        }

        $gpsLocation = GpsLocation::create([
            'driver_id' => $driver->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'recorded_at' => now(),
        ]);

        $assignment = $driver->activeVehicleAssignment()->first();

        $ambulance = $assignment?->ambulance;

        if ($ambulance instanceof Ambulance) {
            $ambulance->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
        } else {
            $dispatch = Dispatch::where('driver_id', $driver->id)
                ->whereIn('status', [Dispatch::STATUS_ASSIGNED, Dispatch::STATUS_ACCEPTED, Dispatch::STATUS_EN_ROUTE, Dispatch::STATUS_ARRIVED])
                ->latest('assigned_at')
                ->first();

            $ambulance = $dispatch?->ambulance;

            if ($ambulance instanceof Ambulance) {
                $ambulance->update([
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'gps_id' => $gpsLocation->id,
            'ambulance_updated' => (bool) $assignment,
        ]);
    }
}
