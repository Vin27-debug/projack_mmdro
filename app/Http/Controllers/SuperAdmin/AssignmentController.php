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
                'drivers' => Driver::all(),
                'ambulances' => Ambulance::all(),
            ]
        );
    }

    public function store(Request $request)
    {
        VehicleDriverAssignment::create([
            'driver_id' => $request->driver_id,
            'ambulance_id' => $request->ambulance_id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);

        return redirect()
            ->route('assignments.index');
    }
}
    