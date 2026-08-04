<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Driver;
use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Models\Notification;
use App\Models\VehicleDriverAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\DispatchRecommendationService;

class IncidentController extends Controller
{
    protected DispatchRecommendationService $recommendationService;

    public function __construct(DispatchRecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }
    public function index()
    {
        $incidents = Incident::latest()->get();

        return view(
            'admin.incidents.index',
            compact('incidents')
        );
    }

    public function create()
    {
        return view('admin.incidents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'reporter_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'incident_type' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'priority' => 'required|in:Low,Medium,High,Critical',
        ]);

        $incident = Incident::create([

            'incident_number' =>
            'INC-' . str_pad(
                Incident::count() + 1,
                3,
                '0',
                STR_PAD_LEFT
            ),

            'reporter_name' =>
            $request->reporter_name,

            'contact_number' =>
            $request->contact_number,

            'incident_type' =>
            $request->incident_type,

            'location' =>
            $request->location,

            'description' =>
            $request->description,

            'latitude' =>
            $request->filled('latitude') ? $request->latitude : null,

            'longitude' =>
            $request->filled('longitude') ? $request->longitude : null,

            'priority' =>
            $request->priority,

            'status' =>
            'pending'
        ]);

        Notification::create([
            'title' => 'New Incident Reported',
            'message' => 'A new incident ' . $incident->incident_number . ' has been reported and requires attention.',
            'type' => 'incident',
            'is_read' => false,
        ]);

        return redirect()
            ->route('admin.incidents.index')
            ->with('success', 'Incident Created');
    }


    public function dispatchForm(Incident $incident)
    {
        $drivers = Driver::where(
            'status',
            'available'
        )->get();

        $vehicles = Ambulance::where(
            'status',
            'available'
        )->get();

        $recommendation = $this->recommendationService->recommend($incident, $drivers, $vehicles);

        $nearestDriver = $recommendation['nearestDriver'];
        $nearestDistance = $recommendation['nearestDriverDistance'];
        $nearestAmbulance = $recommendation['nearestAmbulance'];
        $nearestAmbulanceDistance = $recommendation['nearestAmbulanceDistance'];

        return view(
            'admin.incidents.dispatch',
            compact(
                'incident',
                'drivers',
                'vehicles',
                'nearestDriver',
                'nearestDistance',
                'nearestAmbulance',
                'nearestAmbulanceDistance'
            )
        );
    }

    public function dispatch(
        Request $request,
        Incident $incident
    ) {
        $request->validate([
            'driver_id'  => 'required|exists:drivers,id',
            'vehicle_id' => 'required|exists:ambulances,id',
            'status'     => ['nullable', Rule::in(Dispatch::validStatuses())],
        ]);

        $driverId = (int) $request->driver_id;
        $vehicleId = (int) $request->vehicle_id;
        $dispatchStatus = $request->input('status', Dispatch::STATUS_ASSIGNED);

        if (Dispatch::active()->where('incident_id', $incident->id)->exists()) {
            return back()->with('error', 'This incident already has an active dispatch.');
        }

        if (Dispatch::active()->where('driver_id', $driverId)->exists()) {
            return back()->with('error', 'This driver already has an active dispatch.');
        }

        if (Dispatch::active()->where('vehicle_id', $vehicleId)->exists()) {
            return back()->with('error', 'This ambulance already has an active dispatch.');
        }

        $driverStatus = match ($dispatchStatus) {
            Dispatch::STATUS_ASSIGNED => Driver::STATUS_AVAILABLE,

            Dispatch::STATUS_ACCEPTED,
            Dispatch::STATUS_EN_ROUTE => Driver::STATUS_EN_ROUTE,

            Dispatch::STATUS_ARRIVED => Driver::STATUS_ON_SCENE,

            default => Driver::STATUS_AVAILABLE,
        };

        DB::transaction(function () use ($incident, $driverId, $vehicleId, $dispatchStatus, $driverStatus) {
            Dispatch::updateOrCreate(
                [
                    'incident_id' => $incident->id,
                    'driver_id' => $driverId,
                    'vehicle_id' => $vehicleId,
                    'status' => $dispatchStatus,
                ],
                [
                    'assigned_at' => now(),
                ]
            );

            $driver = Driver::find($driverId);
            $ambulance = Ambulance::find($vehicleId);

            if ($driver) {
                $driver->update([
                    'status' => $driverStatus,
                ]);
            }

            if ($ambulance) {
                $ambulance->update([
                    'status' => Ambulance::STATUS_ON_DUTY,
                ]);
            }

            if ($driver && $ambulance) {
                VehicleDriverAssignment::assignDriverToAmbulance($driver, $ambulance);
            }

            $incident->update([
                'status' => Incident::STATUS_DISPATCHED,
                'driver_id' => $driverId,
                'ambulance_id' => $vehicleId,
            ]);
        });

        return redirect()
            ->route('admin.incidents.index')
            ->with(
                'success',
                'Vehicle dispatched successfully.'
            );
    }
}
