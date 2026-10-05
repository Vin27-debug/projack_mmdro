<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Ambulance;
use App\Models\VehicleDriverAssignment;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index()
    {
        $assignments = VehicleDriverAssignment::with([
            'driver.user',
            'ambulance'
        ])->get();

        return view(
            'superadmin.assignments.index',
            compact('assignments')
        );
    }

    public function create()
    {
        return view(
            'superadmin.assignments.create',
            [
                'drivers' => Driver::activeAccount()->get(),
                'ambulances' => Ambulance::available()->get(),
            ]
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'driver_id' => ['required', 'integer', 'exists:drivers,id'],
            'ambulance_id' => ['required', 'integer', 'exists:ambulances,id'],
        ]);

        $driver = Driver::activeAccount()->find($validated['driver_id']);
        abort_unless($driver, 422, 'Only active drivers can be assigned.');

        $ambulance = Ambulance::available()->find($validated['ambulance_id']);
        abort_unless($ambulance, 422, 'Only available vehicles can be assigned.');

        VehicleDriverAssignment::create([
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);

        return redirect()
            ->route('assignments.index');
    }
}
    