<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Dispatch;
use Illuminate\Http\Request;

class AmbulanceController extends Controller
{
    public function index()
    {
        $archived = request()->boolean('archived');
        $ambulances = ($archived ? Ambulance::archived() : Ambulance::notArchived())
            ->latest()
            ->get();

        return view(
            'superadmin.ambulances.index',
            compact('ambulances', 'archived')
        );
    }

    public function create()
    {
        return view('superadmin.ambulances.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'plate_number' => 'required|unique:ambulances',
            'vehicle_name' => 'required',
            'vehicle_type' => 'required',
        ]);

        Ambulance::create([
            'plate_number' => $request->plate_number,
            'vehicle_name' => $request->vehicle_name,
            'vehicle_type' => $request->vehicle_type,
            'status' => 'available',
        ]);

        return redirect()
            ->route('superadmin.ambulances.index')
            ->with('success', 'Ambulance added successfully.');
    }

    public function edit(Ambulance $ambulance)
    {
        abort_if($ambulance->archived_at, 404);

        return view(
            'superadmin.ambulances.edit',
            compact('ambulance')
        );
    }

    public function update(Request $request, Ambulance $ambulance)
    {
        abort_if($ambulance->archived_at, 404);
        $validated = $request->validate([
            'plate_number' => 'required',
            'vehicle_name' => 'required',
            'vehicle_type' => 'required|in:ambulance,rescue_van,fire_truck',
            'status' => 'required|in:available,on_duty,maintenance',
        ]);

        $ambulance->update($validated);

        return redirect()
            ->route('superadmin.ambulances.index')
            ->with('success', 'Ambulance updated successfully.');
    }
    
    public function archive(Ambulance $ambulance)
    {
        abort_if($ambulance->archived_at, 404);

        if (Dispatch::active()->where('vehicle_id', $ambulance->id)->exists()) {
            return back()->with('error', 'A vehicle with an active dispatch cannot be archived.');
        }

        $ambulance->update([
            'archived_at' => now(),
            'archived_by' => auth()->id(),
        ]);

        return back()
            ->with('success', 'Vehicle archived. Historical dispatch and maintenance records remain available.');
    }

    public function restore(Ambulance $ambulance)
    {
        abort_if(!$ambulance->archived_at, 404);

        $ambulance->update([
            'archived_at' => null,
            'archived_by' => null,
        ]);

        return back()->with('success', 'Vehicle restored.');
    }
}
