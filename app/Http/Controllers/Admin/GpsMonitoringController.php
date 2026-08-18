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

   
    public function locations()
    {
       
        $drivers = Driver::with([
            'user',
            'activeVehicleAssignment.ambulance',
        ])->get();

       
        $activeDispatches = Dispatch::with([
            'incident',
            'driver.user',
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

        $locations = collect();

        foreach ($drivers as $driver) {

          
            $location = GpsLocation::where(
                'driver_id',
                $driver->id
            )
                ->latest('recorded_at')
                ->first();

           
            if (!$location) {
                continue;
            }

            
            $dispatch = $activeDispatches->get($driver->id);

            $incident = $dispatch?->incident;

           
            $assignedAmbulance =
                $driver->activeVehicleAssignment?->ambulance;

           
            $ambulance =
                $dispatch?->ambulance
                ?? $assignedAmbulance;

            
            $missionStatus = $dispatch
                ? $dispatch->status
                : 'no_mission';

           
            $locations->push([

                /*
                 * =========================
                 * DRIVER
                 * =========================
                 */
                'driver_id' => $driver->id,

                'driver_name' =>
                    $driver->user?->name
                    ?? 'Unknown Driver',

                /*
                 * =========================
                 * VEHICLE
                 * =========================
                 */
                'vehicle_id' =>
                    $ambulance?->id,

                'vehicle_name' =>
                    $ambulance?->vehicle_name
                    ?? 'Ambulance',

                'vehicle_status' =>
                    $ambulance?->status
                    ?? 'UNKNOWN',

                /*
                 * =========================
                 * MISSION
                 * =========================
                 */
                'has_active_mission' =>
                    $dispatch !== null,

                'dispatch_id' =>
                    $dispatch?->id,

                'dispatch_status' =>
                    $missionStatus,

                /*
                 * =========================
                 * INCIDENT
                 * =========================
                 */
                'incident_id' =>
                    $incident?->id,

                'incident_number' =>
                    $incident?->incident_number,

                'incident_location' =>
                    $incident?->location,

                /*
                 * NEVER return 0,0
                 * when there is no incident.
                 */
                'incident_latitude' =>
                    $incident?->latitude !== null
                        ? (float) $incident->latitude
                        : null,

                'incident_longitude' =>
                    $incident?->longitude !== null
                        ? (float) $incident->longitude
                        : null,

                /*
                 * =========================
                 * CURRENT GPS LOCATION
                 * =========================
                 */
                'latitude' =>
                    (float) $location->latitude,

                'longitude' =>
                    (float) $location->longitude,

                'recorded_at' =>
                    $location->recorded_at?->toISOString(),
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