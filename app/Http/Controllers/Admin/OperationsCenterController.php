<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Models\HijackAlert;
use App\Models\Incident;
use App\Models\PanicAlert;

class OperationsCenterController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | INCIDENTS
        |--------------------------------------------------------------------------
        */

        $incidents = Incident::query()
            ->with([
                'driver.user',
                'ambulance',
            ])
            ->latest()
            ->take(50)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | VEHICLES / AMBULANCES
        |--------------------------------------------------------------------------
        */

        $vehicles = Ambulance::query()
            ->latest()
            ->take(50)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | PANIC ALERTS
        |--------------------------------------------------------------------------
        */

        $panicAlerts = PanicAlert::query()
            ->with('driver.user')
            ->latest()
            ->take(20)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | HIJACK ALERTS
        |--------------------------------------------------------------------------
        */

        $hijackAlerts = HijackAlert::query()
            ->with('driver.user')
            ->latest()
            ->take(20)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | ACTIVE DISPATCHES
        |--------------------------------------------------------------------------
        */

        $activeDispatches = Dispatch::query()
            ->with([
                'driver.user',
                'vehicle',
                'incident',
            ])
            ->whereNotIn('status', [
                'completed',
                'cancelled',
            ])
            ->latest()
            ->take(10)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | STATISTICS
        |--------------------------------------------------------------------------
        */

        $stats = [

            'live_incidents' => $incidents
                ->whereIn('status', [
                    'pending',
                    'assigned',
                    'in_progress',
                    'accepted',
                    'en_route',
                    'responding',
                ])
                ->count(),

            'active_ambulances' => $vehicles
                ->whereIn('status', [
                    'available',
                    'on_duty',
                    'active',
                    'ready',
                ])
                ->count(),

            'panic_alerts' => $panicAlerts
                ->where('resolved', false)
                ->count(),

            'hijack_alerts' => $hijackAlerts
                ->where('status', 'active')
                ->count(),

            'priority_incidents' => $incidents
                ->filter(function ($incident) {

                    return in_array(
                        $incident->incident_type,
                        [
                            'Fire',
                            'Medical',
                            'Rescue',
                        ],
                        true
                    );
                })
                ->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | MAP DATA
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | We only send records with valid latitude and longitude.
        |
        */

        $mapData = [

            /*
            |--------------------------------------------------------------------------
            | INCIDENTS
            |--------------------------------------------------------------------------
            */

            'incidents' => $incidents
                ->filter(function ($incident) {

                    return is_numeric($incident->latitude)
                        && is_numeric($incident->longitude)
                        && $incident->latitude >= -90
                        && $incident->latitude <= 90
                        && $incident->longitude >= -180
                        && $incident->longitude <= 180;
                })
                ->map(function ($incident) {

                    return [
                        'id' => $incident->id,

                        'type' => 'incident',

                        'title' => $incident->incident_number
                            ?? 'Incident',

                        'location' => $incident->location
                            ?? 'Unknown location',

                        'status' => $incident->status
                            ?? 'unknown',

                        'priority' => in_array(
                            $incident->incident_type,
                            [
                                'Fire',
                                'Medical',
                                'Rescue',
                            ],
                            true
                        )
                            ? 'high'
                            : 'standard',

                        'latitude' => (float) $incident->latitude,

                        'longitude' => (float) $incident->longitude,

                        'driver_name' => $incident->driver?->user?->name
                            ?? 'Unassigned',
                    ];
                })
                ->values()
                ->all(),


            /*
            |--------------------------------------------------------------------------
            | VEHICLES
            |--------------------------------------------------------------------------
            */

            'vehicles' => $vehicles
                ->filter(function ($vehicle) {

                    return is_numeric($vehicle->latitude)
                        && is_numeric($vehicle->longitude)
                        && $vehicle->latitude >= -90
                        && $vehicle->latitude <= 90
                        && $vehicle->longitude >= -180
                        && $vehicle->longitude <= 180;
                })
                ->map(function ($vehicle) {

                    return [
                        'id' => $vehicle->id,

                        'type' => 'vehicle',

                        'title' => $vehicle->vehicle_name
                            ?? 'Ambulance',

                        'plate_number' => $vehicle->plate_number
                            ?? 'N/A',

                        'status' => $vehicle->status
                            ?? 'unknown',

                        'latitude' => (float) $vehicle->latitude,

                        'longitude' => (float) $vehicle->longitude,
                    ];
                })
                ->values()
                ->all(),


            /*
            |--------------------------------------------------------------------------
            | PANIC ALERTS
            |--------------------------------------------------------------------------
            */

            'panicAlerts' => $panicAlerts
                ->filter(function ($alert) {

                    return is_numeric($alert->latitude)
                        && is_numeric($alert->longitude)
                        && $alert->latitude >= -90
                        && $alert->latitude <= 90
                        && $alert->longitude >= -180
                        && $alert->longitude <= 180;
                })
                ->map(function ($alert) {

                    return [
                        'id' => $alert->id,

                        'type' => 'panic',

                        'title' => 'Panic Alert',

                        'status' => $alert->resolved ? 'resolved' : 'active',

                        'latitude' => (float) $alert->latitude,

                        'longitude' => (float) $alert->longitude,

                        'driver_name' => $alert->driver?->user?->name
                            ?? 'Driver',

                        'triggered_at' => $alert->triggered_at
                            ? $alert->triggered_at->format('M d, H:i')
                            : 'Recently',
                    ];
                })
                ->values()
                ->all(),


            /*
            |--------------------------------------------------------------------------
            | HIJACK ALERTS
            |--------------------------------------------------------------------------
            */

            'hijackAlerts' => $hijackAlerts
                ->filter(function ($alert) {

                    return is_numeric($alert->latitude)
                        && is_numeric($alert->longitude)
                        && $alert->latitude >= -90
                        && $alert->latitude <= 90
                        && $alert->longitude >= -180
                        && $alert->longitude <= 180;
                })
                ->map(function ($alert) {

                    return [
                        'id' => $alert->id,

                        'type' => 'hijack',

                        'title' => 'Hijack Alert',

                        'status' => $alert->status
                            ?? 'active',

                        'latitude' => (float) $alert->latitude,

                        'longitude' => (float) $alert->longitude,

                        'driver_name' => $alert->driver?->user?->name
                            ?? 'Driver',

                        'triggered_at' => $alert->triggered_at
                            ? $alert->triggered_at->format('M d, H:i')
                            : 'Recently',
                    ];
                })
                ->values()
                ->all(),
        ];


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.operations-center',
            compact(
                'incidents',
                'vehicles',
                'panicAlerts',
                'hijackAlerts',
                'activeDispatches',
                'stats',
                'mapData'
            )
        );
    }
}
