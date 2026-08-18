<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AmbulanceController extends Controller
{
    public function index()
    {
        $ambulances = Ambulance::latest()->get();

        return view('admin.ambulances.index', compact('ambulances'));
    }

    public function create()
    {
        return view('admin.ambulances.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plate_number' => ['required', 'string', 'max:255', 'unique:ambulances,plate_number'],
            'vehicle_name' => ['required', 'string', 'max:255'],
            'vehicle_type' => [
                'required',
                Rule::in([
                    'ambulance',
                    'rescue_van',
                    'fire_truck',
                    'police',
                ]),
            ],
        ]);

        $data['status'] = Ambulance::STATUS_AVAILABLE;

        Ambulance::create($data);

        return redirect()
            ->route('admin.ambulances.index')
            ->with('success', 'Vehicle added successfully.');
    }

    public function edit(Ambulance $ambulance)
    {
        return view('admin.ambulances.edit', compact('ambulance'));
    }

    public function update(Request $request, Ambulance $ambulance)
    {
        $data = $request->validate([
            'plate_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ambulances', 'plate_number')
                    ->ignore($ambulance->id),
            ],
            'vehicle_name' => ['required', 'string', 'max:255'],
            'vehicle_type' => [
                'required',
                Rule::in([
                    'ambulance',
                    'rescue_van',
                    'fire_truck',
                    'police',
                ]),
            ],
            'status' => [
                'required',
                Rule::in(Ambulance::VALID_STATUSES),
            ],
        ]);

        $ambulance->update($data);

        return redirect()
            ->route('admin.ambulances.index')
            ->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Ambulance $ambulance)
    {
        if ($ambulance->dispatches()->exists()) {
            return back()->with(
                'error',
                'This vehicle cannot be deleted because it has dispatch records.'
            );
        }

        $ambulance->delete();

        return back()->with(
            'success',
            'Vehicle deleted successfully.'
        );
    }
}
