<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\GpsLocation;

class GpsMonitoringController extends Controller
{
    public function index()
    {
        return view('admin.gps-monitoring');
    }

    /**
     * Return latest GPS location for every driver that has GPS data.
     *
     * GPS tracking is independent from dispatch status.
     * Therefore, even drivers without an active mission can still
     * appear on the admin monitoring map using their latest GPS location.
     */
    public function locations()
    {
        $drivers = Driver::with([
            'user',
            'activeVehicleAssignment.ambulance',
        ])->get();

        /*
        |--------------------------------------------------------------------------
        | Active dispatches
        |--------------------------------------------------------------------------
        */

        $dispatches = Dispatch::with([
            'incident',
            'ambulance',
        ])
            ->whereIn('status', [
                Dispatch::STATUS_PENDING,
                Dispatch::STATUS_ASSIGNED,
                Dispatch::STATUS_ACCEPTED,
                Dispatch::STATUS_EN_ROUTE,
                Dispatch::STATUS_ARRIVED,
            ])
            ->latest('created_at')
            ->get()
            ->keyBy('driver_id');

        /*
        |--------------------------------------------------------------------------
        | Latest GPS locations
        |--------------------------------------------------------------------------
        |
        | Instead of querying GpsLocation inside every driver loop,
        | retrieve the latest location for each driver.
        |
        */

        $latestLocations = collect();

        foreach ($drivers as $driver) {
            $location = GpsLocation::where(
                'driver_id',
                $driver->id
            )
                ->latest('recorded_at')
                ->first();

            if ($location) {
                $latestLocations->put(
                    $driver->id,
                    $location
                );
            }
        }

        $locations = collect();

        foreach ($drivers as $driver) {
            /*
            |--------------------------------------------------------------------------
            | Latest GPS
            |--------------------------------------------------------------------------
            */

            $location = $latestLocations->get(
                $driver->id
            );

            if (!$location) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Active dispatch
            |--------------------------------------------------------------------------
            */

            $dispatch = $dispatches->get(
                $driver->id
            );

            $incident = $dispatch?->incident;

            /*
            |--------------------------------------------------------------------------
            | Ambulance
            |--------------------------------------------------------------------------
            */

            $assignedAmbulance =
                $driver->activeVehicleAssignment?->ambulance;

            $ambulance =
                $dispatch?->ambulance
                ?? $assignedAmbulance;

            /*
            |--------------------------------------------------------------------------
            | Mission status
            |--------------------------------------------------------------------------
            */

            $missionStatus = $dispatch
                ? $dispatch->status
                : 'no_mission';

            /*
            |--------------------------------------------------------------------------
            | Push GPS information
            |--------------------------------------------------------------------------
            */

            $locations->push([

                /*
                |--------------------------------------------------------------------------
                | DRIVER
                |--------------------------------------------------------------------------
                */

                'driver_id' => $driver->id,

                'driver_name' =>
                $driver->user?->name
                    ?? 'Unknown Driver',

                /*
                |--------------------------------------------------------------------------
                | VEHICLE
                |--------------------------------------------------------------------------
                */

                'vehicle_id' =>
                $ambulance?->id,

                'vehicle_name' =>
                $ambulance?->vehicle_name
                    ?? $ambulance?->plate_number
                    ?? 'Ambulance',

                'vehicle_plate' =>
                $ambulance?->plate_number,

                'vehicle_status' =>
                $ambulance?->status
                    ?? 'UNKNOWN',

                /*
                |--------------------------------------------------------------------------
                | MISSION
                |--------------------------------------------------------------------------
                */

                'has_active_mission' =>
                $dispatch !== null,

                'dispatch_id' =>
                $dispatch?->id,

                'dispatch_status' =>
                $missionStatus,

                'monitoring_status' =>
                $missionStatus,

                /*
                |--------------------------------------------------------------------------
                | INCIDENT
                |--------------------------------------------------------------------------
                */

                'incident_id' =>
                $incident?->id,

                'incident_number' =>
                $incident?->incident_number,

                'incident_location' =>
                $incident?->location,

                'incident_address' => $incident ? collect([$incident->house_number, $incident->street, $incident->barangay, $incident->city, $incident->province])->filter()->implode(', ') : null,

                'incident_latitude' =>
                $incident?->latitude !== null
                    ? (float) $incident->latitude
                    : null,

                'incident_longitude' =>
                $incident?->longitude !== null
                    ? (float) $incident->longitude
                    : null,

                /*
                |--------------------------------------------------------------------------
                | CURRENT GPS
                |--------------------------------------------------------------------------
                */

                'latitude' =>
                (float) $location->latitude,

                'longitude' =>
                (float) $location->longitude,

                'recorded_at' =>
                $location->recorded_at?->toISOString(),

                'speed_kmh' => $location->speed_kmh,
                'speed_status' => $location->speed_status,
                'speed_limit_kmh' => $location->speed_limit_kmh,
            ]);
        }

        return response()->json(
            $locations->values()
        );
    }

    /**
     * GPS history page.
     */
    public function history()
    {
        $locations = GpsLocation::with([
            'driver.user',
        ])
            ->latest('recorded_at')
            ->paginate(100);

        return view(
            'admin.gps-history',
            compact('locations')
        );
    }
}
