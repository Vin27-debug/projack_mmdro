<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Models\Incident;
use App\Models\IncidentAttachment;
use App\Models\Driver;
use App\Models\Notification;
use App\Models\VehicleDriverAssignment;
use App\Services\DispatchRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class IncidentController extends Controller
{
    public function __construct(protected DispatchRecommendationService $recommendationService) {}

    public function index(Request $request)
    {
        $showArchived = $request->boolean('archived');

        $incidents = Incident::query()
            ->with(['driver.user', 'ambulance'])
            ->when($showArchived, fn($query) => $query->archived(), fn($query) => $query->notArchived())
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = trim($request->input('search'));
                $query->where(function ($q) use ($term): void {
                    $q->where('incident_number', 'like', "%{$term}%")
                        ->orWhere('reporter_name', 'like', "%{$term}%")
                        ->orWhere('location', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('type'), fn($query) => $query->where('incident_type', $request->input('type')))
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('start_date'), fn($query) => $query->whereDate('created_at', '>=', $request->input('start_date')))
            ->when($request->filled('end_date'), fn($query) => $query->whereDate('created_at', '<=', $request->input('end_date')))
            ->latest()
            ->get();

        $incidentTypes = Incident::query()->select('incident_type')->whereNotNull('incident_type')->distinct()->orderBy('incident_type')->pluck('incident_type');

        return view('admin.incidents.index', compact('incidents', 'incidentTypes', 'showArchived'));
    }

    public function create()
    {
        return view('admin.incidents.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateIncident($request);

        [$data['latitude'], $data['longitude']] = $this->resolveCoordinates($request);

        $incident = DB::transaction(function () use ($data, $request) {
            $incident = Incident::create([
                ...$data,
                'incident_number' => 'INC-' . str_pad(Incident::count() + 1, 3, '0', STR_PAD_LEFT),
                'status' => Incident::STATUS_PENDING,
                'call_received_at' => now(),
            ]);

            $this->storeAttachments($request, $incident);

            Notification::create([
                'title' => 'New Incident Reported',
                'message' => 'A new incident ' . $incident->incident_number . ' has been reported and requires attention.',
                'type' => 'incident',
                'is_read' => false,
            ]);

            return $incident;
        });

        return redirect()->route('admin.incidents.show', $incident)->with('success', 'Incident created successfully.');
    }

    public function show(Incident $incident)
    {
        $incident->load(['driver.user', 'ambulance', 'dispatches.driver.user', 'attachments.uploader']);

        return view('admin.incidents.show', compact('incident'));
    }

    public function edit(Incident $incident)
    {
        return view('admin.incidents.edit', compact('incident'));
    }

    public function update(Request $request, Incident $incident)
    {
        if ($incident->archived_at) {
            return back()->with('error', 'Archived incidents are read-only. Restore the incident first before editing.');
        }

        $data = $this->validateIncident($request);
        [$data['latitude'], $data['longitude']] = $this->resolveCoordinates($request);

        $incident->update($data);
        $this->storeAttachments($request, $incident);

        return redirect()->route('admin.incidents.show', $incident)->with('success', 'Incident updated successfully.');
    }

    public function archive(Incident $incident)
    {
        if ($incident->archived_at) {
            return back()->with('error', 'Incident is already archived.');
        }

        $incident->update([
            'archived_at' => now(),
            'archived_by' => auth()->id(),
            'status' => Incident::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        return back()->with('success', $incident->incident_number . ' has been archived. The record and attachments remain searchable.');
    }

    public function restore(Incident $incident)
    {
        if (!$incident->archived_at) {
            return back()->with('error', 'Incident is not archived.');
        }

        $incident->update([
            'archived_at' => null,
            'archived_by' => null,
            'status' => Incident::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        return back()->with('success', $incident->incident_number . ' has been restored from the archive.');
    }

    public function downloadAttachment(Incident $incident, IncidentAttachment $attachment)
    {
        abort_unless($attachment->incident_id === $incident->id, 404);
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    public function dispatchForm(Incident $incident)
    {
        $drivers = Driver::where('status', 'available')->get();
        $vehicles = Ambulance::where('status', 'available')->get();
        $recommendation = $this->recommendationService->recommend($incident, $drivers, $vehicles);

        $nearestDriver = $recommendation['nearestDriver'];
        $nearestDistance = $recommendation['nearestDriverDistance'];
        $nearestAmbulance = $recommendation['nearestAmbulance'];
        $nearestAmbulanceDistance = $recommendation['nearestAmbulanceDistance'];

        return view('admin.incidents.dispatch', compact(
            'incident',
            'drivers',
            'vehicles',
            'nearestDriver',
            'nearestDistance',
            'nearestAmbulance',
            'nearestAmbulanceDistance'
        ));
    }

    public function dispatch(Request $request, Incident $incident)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_id' => ['nullable', 'exists:ambulances,id'],
            'status' => ['nullable', Rule::in(Dispatch::validStatuses())],
        ]);

        $driverId = (int) $request->driver_id;
        $vehicleId = $request->filled('vehicle_id') ? (int) $request->vehicle_id : null;
        $dispatchStatus = $request->input('status', Dispatch::STATUS_ASSIGNED);

        if (Dispatch::active()->where('incident_id', $incident->id)->exists()) {
            return back()->with('error', 'This incident already has an active dispatch.');
        }
        if (Dispatch::active()->where('driver_id', $driverId)->exists()) {
            return back()->with('error', 'This driver already has an active dispatch.');
        }
        if ($vehicleId && Dispatch::active()->where('vehicle_id', $vehicleId)->exists()) {
            return back()->with('error', 'This ambulance already has an active dispatch.');
        }

        $driverStatus = match ($dispatchStatus) {
            Dispatch::STATUS_ASSIGNED => Driver::STATUS_AVAILABLE,
            Dispatch::STATUS_ACCEPTED, Dispatch::STATUS_EN_ROUTE => Driver::STATUS_EN_ROUTE,
            Dispatch::STATUS_ARRIVED => Driver::STATUS_ON_SCENE,
            default => Driver::STATUS_AVAILABLE,
        };

        DB::transaction(function () use ($incident, $driverId, $vehicleId, $dispatchStatus, $driverStatus) {
            $dispatch = Dispatch::query()->where('incident_id', $incident->id)
                ->where('driver_id', $driverId)
                ->first();

            if ($dispatch) {
                $dispatch->update([
                    'vehicle_id' => $vehicleId,
                    'status' => $dispatchStatus,
                    'assigned_at' => $dispatch->assigned_at ?? now(),
                ]);
            } else {
                $dispatch = Dispatch::create([
                    'incident_id' => $incident->id,
                    'driver_id' => $driverId,
                    'vehicle_id' => $vehicleId,
                    'status' => $dispatchStatus,
                    'assigned_at' => now(),
                ]);
            }

            $driver = Driver::find($driverId);
            $ambulance = $vehicleId ? Ambulance::find($vehicleId) : null;
            $driver?->update(['status' => $driverStatus]);
            if ($ambulance) {
                $ambulance->update(['status' => Ambulance::STATUS_ON_DUTY]);
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

        return redirect()->route('admin.incidents.index')->with('success', 'Driver dispatched successfully.');
    }

    protected function validateIncident(Request $request): array
    {
        $data = $request->validate([
            'reporter_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'incident_type' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'priority' => 'required|in:Low,Medium,High,Critical',
            'status' => ['nullable', Rule::in(Incident::VALID_STATUSES)],
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx',
        ]);

        unset($data['attachments'], $data['status']);

        return $data;
    }

    protected function resolveCoordinates(Request $request): array
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        if ($request->filled('location') && (!$request->filled('latitude') || !$request->filled('longitude'))) {
            try {
                $response = Http::timeout(8)->withHeaders(['User-Agent' => 'MuniResQ/1.0'])->get(
                    'https://nominatim.openstreetmap.org/search',
                    ['q' => $request->input('location') . ', Philippines', 'format' => 'jsonv2', 'addressdetails' => 1, 'limit' => 1, 'countrycodes' => 'ph']
                );

                if ($response->successful() && !empty($response->json())) {
                    $result = $response->json()[0];
                    $latitude = $result['lat'] ?? $latitude;
                    $longitude = $result['lon'] ?? $longitude;
                }
            } catch (\Throwable) {
                // Keep the manually supplied coordinates when geocoding is unavailable.
            }
        }

        return [$latitude, $longitude];
    }

    protected function storeAttachments(Request $request, Incident $incident): void
    {
        if (!$request->hasFile('attachments')) {
            return;
        }

        foreach ($request->file('attachments', []) as $file) {
            if (!$file->isValid()) {
                continue;
            }

            $path = $file->store('incident-attachments/' . $incident->id, 'local');

            IncidentAttachment::create([
                'incident_id' => $incident->id,
                'uploaded_by' => auth()->id(),
                'disk' => 'local',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize() ?: 0,
            ]);
        }
    }
}
