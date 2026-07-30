<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Ambulance;
use App\Models\VehicleDriverAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\AuditService;

class DispatchController extends Controller
{
    public function index()
    {
        $incidents = Incident::latest()->get();
        $drivers = Driver::all();
        $ambulances = Ambulance::all();

        return view(
            'admin.dispatches.index',
            compact('incidents', 'drivers', 'ambulances')
        );
    }

    public function assign(Request $request, Incident $incident)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'ambulance_id' => 'required_without:vehicle_id|exists:ambulances,id',
            'vehicle_id' => 'required_without:ambulance_id|exists:ambulances,id',
        ]);

        $ambulanceId = $request->input('ambulance_id') ?? $request->input('vehicle_id');
        $driverId = (int) $request->driver_id;

        if (Dispatch::active()->where('driver_id', $driverId)->exists()) {
            return back()->with('error', 'This driver already has an active dispatch.');
        }

        if (Dispatch::active()->where('vehicle_id', $ambulanceId)->exists()) {
            return back()->with('error', 'This ambulance already has an active dispatch.');
        }

        if (Dispatch::active()->where('incident_id', $incident->id)->exists()) {
            return back()->with('error', 'This incident already has an active dispatch.');
        }

        DB::transaction(function () use ($incident, $driverId, $ambulanceId) {
            Dispatch::updateOrCreate(
                [
                    'incident_id' => $incident->id,
                    'driver_id' => $driverId,
                    'vehicle_id' => $ambulanceId,
                    'status' => Dispatch::STATUS_ASSIGNED,
                ],
                [
                    'assigned_at' => now(),
                ]
            );

            $incident->update([
                'driver_id' => $driverId,
                'ambulance_id' => $ambulanceId,
                'status' => Incident::STATUS_DISPATCHED,
            ]);

            Driver::whereKey($driverId)->update([
                'status' => Driver::STATUS_AVAILABLE,
            ]);

            Ambulance::whereKey($ambulanceId)->update([
                'status' => Ambulance::STATUS_ON_DUTY,
            ]);
        });

        return back()->with('success', 'Dispatch assigned successfully.');
    }
}
