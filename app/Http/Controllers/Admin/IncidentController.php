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
use App\Services\AuditService;
use App\Services\DispatchRecommendationService;
use Carbon\Carbon;
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

    public function updateTimestamp(Request $request, Incident $incident, string $field)
    {
        abort_unless(auth()->user()?->hasRole(['admin', 'super-admin']), 403, 'You are not authorized to update emergency timestamps.');

        $timestampMap = [
            'incident-reported' => ['target' => 'incident', 'column' => 'created_at', 'label' => 'Incident Reported'],
            'call-received' => ['target' => 'incident', 'column' => 'call_received_at', 'label' => 'Call Received'],
            'dispatch-created' => ['target' => 'dispatch', 'column' => 'created_at', 'label' => 'Dispatch Created'],
            'driver-accepted' => ['target' => 'dispatch', 'column' => 'accepted_at', 'label' => 'Driver Accepted'],
            'response-started' => ['target' => 'incident', 'column' => 'response_at', 'label' => 'Response Started'],
            'en-route' => ['target' => 'dispatch', 'column' => 'en_route_at', 'label' => 'En Route'],
            'arrived-at-scene' => ['target' => 'incident', 'column' => 'at_scene_at', 'label' => 'Arrived at Scene'],
            'at-patient' => ['target' => 'incident', 'column' => 'at_patient_at', 'label' => 'At Patient'],
            'departed-from-scene' => ['target' => 'incident', 'column' => 'depart_scene_at', 'label' => 'Departed from Scene'],
            'arrived-at-hospital' => ['target' => 'incident', 'column' => 'at_hospital_at', 'label' => 'Arrived at Hospital'],
            'return-to-base' => ['target' => 'incident', 'column' => 'return_to_base_at', 'label' => 'Return to Base'],
            'response-completed' => ['target' => 'incident', 'column' => 'completed_at', 'label' => 'Response Completed'],
        ];

        $field = $field === 'at_scene_at' ? 'arrived-at-scene' : $field;

        if ($field === 'bulk') {
            $dispatch = $incident->dispatches()->latest('created_at')->first();
            $validated = $request->validate([
                'timestamps' => ['required', 'array'],
                'timestamps.*' => ['nullable', 'date_format:Y-m-d\\TH:i'],
            ]);
            $submitted = $validated['timestamps'];

            foreach (['dispatch-created', 'driver-accepted', 'en-route'] as $dispatchField) {
                if (!$dispatch && filled($submitted[$dispatchField] ?? null)) {
                    return back()->withErrors(['timestamps' => 'A dispatch record is required before dispatch timestamps can be edited.'])->withInput();
                }
            }

            $sequence = [
                'incident-reported' => null,
                'call-received' => $incident->call_received_at,
                'dispatch-created' => $dispatch?->created_at,
                'driver-accepted' => $dispatch?->accepted_at,
                'response-started' => $incident->response_at,
                'en-route' => $dispatch?->en_route_at,
                'arrived-at-scene' => $incident->at_scene_at,
                'at-patient' => $incident->at_patient_at,
                'departed-from-scene' => $incident->depart_scene_at,
                'arrived-at-hospital' => $incident->at_hospital_at,
                'return-to-base' => $incident->return_to_base_at,
                'response-completed' => $incident->completed_at,
            ];

            foreach ($timestampMap as $event => $definition) {
                if (array_key_exists($event, $submitted)) {
                    $sequence[$event] = filled($submitted[$event])
                        ? Carbon::createFromFormat('Y-m-d\\TH:i', $submitted[$event])
                        : null;
                }
            }

            $previous = null;
            foreach ($sequence as $event => $timestamp) {
                if ($timestamp && $previous && $timestamp->lt($previous['time'])) {
                    return back()->withErrors([
                        'timestamps' => sprintf('%s cannot be earlier than %s.', $timestampMap[$event]['label'], $previous['label']),
                    ])->withInput();
                }

                if ($timestamp) {
                    $previous = ['label' => $timestampMap[$event]['label'], 'time' => $timestamp];
                }
            }

            DB::transaction(function () use ($incident, $dispatch, $submitted, $timestampMap): void {
                foreach ($timestampMap as $event => $definition) {
                    if (!array_key_exists($event, $submitted)) {
                        continue;
                    }

                    $newValue = filled($submitted[$event])
                        ? Carbon::createFromFormat('Y-m-d\\TH:i', $submitted[$event])
                        : null;
                    $record = $definition['target'] === 'dispatch' ? $dispatch : $incident;
                    $oldValue = $record?->getAttribute($definition['column']);
                    $changed = ($oldValue === null) !== ($newValue === null)
                        || ($oldValue && $newValue && !$oldValue->equalTo($newValue));

                    if ($record && $changed) {
                        $record->update([$definition['column'] => $newValue]);

                        if ($event === 'arrived-at-scene' && $dispatch) {
                            $dispatch->update(['arrived_at' => $newValue]);
                        }

                        if ($event === 'response-completed' && $dispatch) {
                            $dispatch->update(['completed_at' => $newValue]);
                        }

                        AuditService::log(
                            'updated',
                            'Emergency Time Record',
                            sprintf(
                                'MDRRMO management user updated Emergency Time Record for %s (incident ID %d, dispatch ID %s). Event: %s. Old: %s. New: %s.',
                                $incident->incident_number,
                                $incident->id,
                                $dispatch?->id ?? 'N/A',
                                $definition['label'],
                                $oldValue?->format('M d, Y h:i A') ?? 'Not yet recorded',
                                $newValue?->format('M d, Y h:i A') ?? 'Not yet recorded'
                            )
                        );
                    }
                }
            });

            return redirect()->route('admin.incidents.show', $incident)->with('success', 'Emergency time record updated.');
        }

        abort_unless(isset($timestampMap[$field]), 404);

        $definition = $timestampMap[$field];
        $dispatch = $incident->dispatches()->latest('created_at')->first();

        if ($definition['target'] === 'dispatch' && !$dispatch) {
            return back()->withErrors(['timestamp' => 'A dispatch record is required before this event can be edited.']);
        }

        $validated = $request->validate([
            'timestamp' => ['required', 'date_format:Y-m-d\\TH:i,Y-m-d H:i:s'],
        ]);

        $timestampFormat = str_contains($validated['timestamp'], 'T')
            ? 'Y-m-d\\TH:i'
            : 'Y-m-d H:i:s';
        $newValue = Carbon::createFromFormat($timestampFormat, $validated['timestamp']);
        $sequence = [
            'incident-reported' => $field === 'incident-reported' ? $newValue : null,
            'call-received' => $incident->call_received_at,
            'dispatch-created' => $dispatch?->created_at,
            'driver-accepted' => $dispatch?->accepted_at,
            'response-started' => $incident->response_at,
            'en-route' => $dispatch?->en_route_at,
            'arrived-at-scene' => $incident->at_scene_at,
            'at-patient' => $incident->at_patient_at,
            'departed-from-scene' => $incident->depart_scene_at,
            'arrived-at-hospital' => $incident->at_hospital_at,
            'return-to-base' => $incident->return_to_base_at,
            'response-completed' => $incident->completed_at,
        ];
        $sequence[$field] = $newValue;

        $previous = null;
        foreach ($sequence as $event => $timestamp) {
            if ($timestamp && $previous && $timestamp->lt($previous['time'])) {
                return back()->withErrors([
                    'timestamp' => sprintf('%s cannot be earlier than %s.', $definition['label'], $previous['label']),
                ])->withInput();
            }

            if ($timestamp) {
                $previous = ['label' => $timestampMap[$event]['label'], 'time' => $timestamp];
            }
        }

        $oldValue = $definition['target'] === 'dispatch'
            ? $dispatch?->getAttribute($definition['column'])
            : $incident->getAttribute($definition['column']);

        if ($definition['target'] === 'dispatch') {
            $dispatch->update([$definition['column'] => $newValue]);
        } else {
            $incident->update([$definition['column'] => $newValue]);
        }

        if ($field === 'arrived-at-scene' && $dispatch) {
            $dispatch->update(['arrived_at' => $newValue]);
        }

        if ($field === 'response-completed' && $dispatch) {
            $dispatch->update(['completed_at' => $newValue]);
        }

        AuditService::log(
            'updated',
            'Emergency Time Record',
            sprintf(
                'MDRRMO management user updated Emergency Time Record for %s (incident ID %d, dispatch ID %s). Event: %s. Old: %s. New: %s.',
                $incident->incident_number,
                $incident->id,
                $dispatch?->id ?? 'N/A',
                $definition['label'],
                $oldValue ? $oldValue->format('M d, Y h:i A') : 'Not yet recorded',
                $newValue->format('M d, Y h:i A')
            )
        );

        return redirect()->route('admin.incidents.show', $incident)->with('success', 'Emergency time record updated.');
    }

    public function downloadAttachment(Incident $incident, IncidentAttachment $attachment)
    {
        abort_unless($attachment->incident_id === $incident->id, 404);
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    public function dispatchForm(Incident $incident)
    {
        $recommendation = $this->recommendationService->recommend($incident);
        $drivers = collect($recommendation['eligibleDrivers']);
        $vehicles = collect($recommendation['eligibleVehicles']);

        $nearestDriver = $recommendation['nearestDriver'];
        $nearestDistance = $recommendation['nearestDriverDistance'];
        $nearestAmbulance = $recommendation['nearestAmbulance'];
        $nearestAmbulanceDistance = $recommendation['nearestAmbulanceDistance'];

        return view('admin.incidents.dispatch', compact(
            'incident',
            'drivers',
            'vehicles',
            'recommendation',
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

        $eligibility = $this->recommendationService->recommend($incident);
        $driver = collect($eligibility['eligibleDrivers'])->firstWhere('id', $driverId);
        if (!$driver) {
            return back()->with('error', 'This driver is not active, has no recent GPS location, or is already assigned.');
        }

        $ambulance = $vehicleId
            ? collect($eligibility['eligibleVehicles'])->firstWhere('id', $vehicleId)
            : null;
        if ($vehicleId && !$ambulance) {
            return back()->with('error', 'This vehicle is not active or currently available.');
        }

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
            $oldStatus = $dispatch?->status;

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

            $driver = Driver::findOrFail($driverId);
            $ambulance = $vehicleId ? Ambulance::findOrFail($vehicleId) : null;
            $driver->update(['status' => $driverStatus]);
            if ($ambulance) {
                $ambulance->update(['status' => Ambulance::STATUS_ON_DUTY]);
            }

            if ($ambulance) {
                VehicleDriverAssignment::assignDriverToAmbulance($driver, $ambulance);
            }

            $incident->update([
                'status' => Incident::STATUS_DISPATCHED,
                'driver_id' => $driverId,
                'ambulance_id' => $vehicleId,
            ]);

            if ($oldStatus !== null && $oldStatus !== $dispatch->status) {
                AuditService::logDispatch($dispatch, 'dispatch_status_changed', $oldStatus);
            } else {
                AuditService::logDispatch($dispatch, 'dispatch_assigned');
            }
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
            'priority' => ['required', Rule::in(Incident::VALID_PRIORITIES)],
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
