<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Driver;
use App\Models\Ambulance;
use App\Models\Dispatch;
use Illuminate\Support\Facades\DB;

class AutoDispatchController extends Controller
{
    public function dispatch(Incident $incident)
    {
        $driver = Driver::where('status', 'available')
            ->first();

        $vehicle = Ambulance::where(
            'status',
            'available'
        )->first();

        if (!$driver || !$vehicle) {
            return back()->with(
                'error',
                'No available driver or vehicle.'
            );
        }

        if (Dispatch::active()->where('incident_id', $incident->id)->exists()) {
            return back()->with('error', 'This incident already has an active dispatch.');
        }

        if (Dispatch::active()->where('driver_id', $driver->id)->exists()) {
            return back()->with('error', 'This driver already has an active dispatch.');
        }

        if (Dispatch::active()->where('vehicle_id', $vehicle->id)->exists()) {
            return back()->with('error', 'This ambulance already has an active dispatch.');
        }

        DB::transaction(function () use ($incident, $driver, $vehicle) {
            Dispatch::updateOrCreate(
                [
                    'incident_id' => $incident->id,
                    'driver_id' => $driver->id,
                    'vehicle_id' => $vehicle->id,
                    'status' => Dispatch::STATUS_PENDING,
                ],
                [
                    'assigned_at' => now(),
                ]
            );

            $incident->update([
                'driver_id' => $driver->id,
                'ambulance_id' => $vehicle->id,
                'status' => Incident::STATUS_DISPATCHED,
            ]);

            $driver->update([
                'status' => Driver::STATUS_ASSIGNED,
            ]);

            $vehicle->update([
                'status' => Ambulance::STATUS_ON_DUTY,
            ]);
        });

        return back()->with(
            'success',
            'Vehicle dispatched successfully.'
        );
    }
}
