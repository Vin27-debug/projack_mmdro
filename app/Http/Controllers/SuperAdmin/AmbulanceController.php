<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use Illuminate\Http\Request;

class AmbulanceController extends Controller
{
    public function index()
    {
        $ambulances = Ambulance::latest()->get();

        return view(
            'superadmin.ambulances.index',
            compact('ambulances')
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
        return view(
            'superadmin.ambulances.edit',
            compact('ambulance')
        );
    }

    public function update(Request $request, Ambulance $ambulance)
    {
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
    
    public function destroy(Ambulance $ambulance)
    {
        $ambulance->delete();

        return back()
            ->with('success', 'Ambulance deleted successfully.');
    }
}
