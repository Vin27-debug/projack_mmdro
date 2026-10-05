<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Dispatch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AmbulanceController extends Controller
{
    public function index()
    {
        $archived = request()->boolean('archived');
        $ambulances = ($archived ? Ambulance::archived() : Ambulance::notArchived())
            ->latest()
            ->get();

        return view('admin.ambulances.index', compact('ambulances', 'archived'));
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
        abort_if($ambulance->archived_at, 404);

        return view('admin.ambulances.edit', compact('ambulance'));
    }

    public function update(Request $request, Ambulance $ambulance)
    {
        abort_if($ambulance->archived_at, 404);

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

    public function archive(Ambulance $ambulance)
    {
        abort_if($ambulance->archived_at, 404);

        if (Dispatch::active()->where('vehicle_id', $ambulance->id)->exists()) {
            return back()->with(
                'error',
                'A vehicle with an active dispatch cannot be archived.'
            );
        }

        $ambulance->update([
            'archived_at' => now(),
            'archived_by' => auth()->id(),
        ]);

        return back()->with(
            'success',
            'Vehicle archived. Historical dispatch and maintenance records remain available.'
        );
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
