<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Models\HijackAlert;
use App\Models\Incident;
use App\Models\PanicAlert;
use Illuminate\Support\Collection;

class OperationsCenterController extends Controller
{
    public function index()
    {
        $incidents = Incident::query()
            ->with(['driver.user', 'ambulance'])
            ->latest()
            ->take(50)
            ->get();

        $vehicles = Ambulance::query()
            ->latest()
            ->take(50)
            ->get();

        $panicAlerts = PanicAlert::query()
            ->with('driver.user')
            ->latest()
            ->take(20)
            ->get();

        $hijackAlerts = HijackAlert::query()
            ->with('driver.user')
            ->latest()
            ->take(20)
            ->get();

        $activeDispatches = Dispatch::query()
            ->with(['driver.user', 'vehicle', 'incident'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'live_incidents' => $incidents->whereIn('status', ['pending', 'assigned', 'in_progress', 'accepted', 'en_route', 'responding'])->count(),
            'active_ambulances' => $vehicles->whereIn('status', ['available', 'on_duty', 'active', 'ready'])->count(),
            'panic_alerts' => $panicAlerts->where('status', 'active')->count(),
            'hijack_alerts' => $hijackAlerts->where('status', 'active')->count(),
            'priority_incidents' => $incidents->filter(fn(Incident $incident): bool => in_array($incident->incident_type, ['Fire', 'Medical', 'Rescue'], true))->count(),
        ];

        $mapData = [
            'incidents' => $incidents->map(function (Incident $incident): array {
                return [
                    'id' => $incident->id,
                    'type' => 'incident',
                    'title' => $incident->incident_number,
                    'location' => $incident->location,
                    'status' => $incident->status,
                    'priority' => in_array($incident->incident_type, ['Fire', 'Medical', 'Rescue'], true) ? 'high' : 'standard',
                    'latitude' => $incident->latitude,
                    'longitude' => $incident->longitude,
                    'color' => $incident->status === 'completed' ? 'green' : 'red',
                    'driver_name' => $incident->driver?->user?->name ?? 'Unassigned',
                ];
            })->values(),
            'vehicles' => $vehicles->map(function (Ambulance $vehicle): array {
                return [
                    'id' => $vehicle->id,
                    'type' => 'vehicle',
                    'title' => $vehicle->vehicle_name,
                    'plate_number' => $vehicle->plate_number,
                    'status' => $vehicle->status,
                    'latitude' => $vehicle->latitude,
                    'longitude' => $vehicle->longitude,
                    'color' => 'blue',
                ];
            })->values(),
            'panicAlerts' => $panicAlerts->map(function (PanicAlert $alert): array {
                return [
                    'id' => $alert->id,
                    'type' => 'panic',
                    'title' => 'Panic Alert',
                    'status' => $alert->status ?? 'active',
                    'latitude' => $alert->latitude,
                    'longitude' => $alert->longitude,
                    'color' => 'orange',
                    'driver_name' => $alert->driver?->user?->name ?? 'Driver',
                    'triggered_at' => $alert->triggered_at?->format('M d, H:i'),
                ];
            })->values(),
            'hijackAlerts' => $hijackAlerts->map(function (HijackAlert $alert): array {
                return [
                    'id' => $alert->id,
                    'type' => 'hijack',
                    'title' => 'Hijack Alert',
                    'status' => $alert->status ?? 'active',
                    'latitude' => $alert->latitude,
                    'longitude' => $alert->longitude,
                    'color' => 'black',
                    'driver_name' => $alert->driver?->user?->name ?? 'Driver',
                    'triggered_at' => $alert->triggered_at?->format('M d, H:i'),
                ];
            })->values(),
        ];

        return view('admin.operations-center', compact(
            'incidents',
            'vehicles',
            'panicAlerts',
            'hijackAlerts',
            'activeDispatches',
            'stats',
            'mapData'
        ));
    }
}
