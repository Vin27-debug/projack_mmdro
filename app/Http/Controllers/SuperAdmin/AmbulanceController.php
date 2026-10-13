<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Services\AuditService;
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
        $data = $request->validate([
            'plate_number' => ['required', 'string', 'max:255', 'unique:ambulances,plate_number'],
            'vehicle_name' => ['required', 'string', 'max:255'],
            'vehicle_type' => ['required', Rule::in(Ambulance::VALID_TYPES)],
        ]);

        $data['status'] = Ambulance::STATUS_AVAILABLE;
        $vehicle = Ambulance::create($data);
        AuditService::log('created', 'Vehicle', sprintf('Vehicle ID %d added as %s.', $vehicle->id, $vehicle->vehicle_type));

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
            'vehicle_type' => ['required', Rule::in(Ambulance::VALID_TYPES)],
            'status' => 'required|in:available,on_duty,maintenance',
        ]);

        $ambulance->update($validated);
        AuditService::log('updated', 'Vehicle', sprintf('Vehicle ID %d updated. Changed fields: %s.', $ambulance->id, implode(', ', array_keys($ambulance->getChanges()))));

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
        AuditService::log('archived', 'Vehicle', sprintf('Vehicle ID %d archived.', $ambulance->id));

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
        AuditService::log('restored', 'Vehicle', sprintf('Vehicle ID %d restored.', $ambulance->id));

        return back()->with('success', 'Vehicle restored.');
    }
}
