<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Ambulance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DispatchController extends Controller
{
    /**
     * Dispatch Center
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Only show incidents that still need dispatch
        |--------------------------------------------------------------------------
        |
        | Completed, closed, and cancelled incidents must NEVER appear here.
        |
        */

        $incidents = Incident::whereIn('status', [
            Incident::STATUS_PENDING,
        ])
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Only available drivers
        |--------------------------------------------------------------------------
        */

        $drivers = Driver::where(
            'status',
            Driver::STATUS_AVAILABLE
        )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Only available ambulances
        |--------------------------------------------------------------------------
        */

        $ambulances = Ambulance::where(
            'status',
            Ambulance::STATUS_AVAILABLE
        )
            ->get();


        return view(
            'admin.dispatches.index',
            compact(
                'incidents',
                'drivers',
                'ambulances'
            )
        );
    }


    /**
     * Assign incident to driver and ambulance
     */
    public function assign(
        Request $request,
        Incident $incident
    ) {
        /*
        |--------------------------------------------------------------------------
        | SECURITY CHECK
        |--------------------------------------------------------------------------
        |
        | A completed / closed / cancelled incident
        | must NEVER be dispatchable again.
        |
        */

        if (in_array($incident->status, [
            Incident::STATUS_COMPLETED,
            Incident::STATUS_CLOSED,
            Incident::STATUS_CANCELLED,
        ], true)) {

            return back()->with(
                'error',
                'This incident is already finished and cannot be dispatched again.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate request
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'driver_id' => [
                'required',
                'exists:drivers,id',
            ],

            'ambulance_id' => [
                'required_without:vehicle_id',
                'exists:ambulances,id',
            ],

            'vehicle_id' => [
                'required_without:ambulance_id',
                'exists:ambulances,id',
            ],
        ]);


        $ambulanceId =
            $request->input('ambulance_id')
            ?? $request->input('vehicle_id');

        $driverId =
            (int) $request->driver_id;


        /*
        |--------------------------------------------------------------------------
        | Check driver
        |--------------------------------------------------------------------------
        */

        $driver = Driver::findOrFail($driverId);

        if (
            $driver->status !== Driver::STATUS_AVAILABLE
        ) {

            return back()->with(
                'error',
                'This driver is currently not available.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Check ambulance
        |--------------------------------------------------------------------------
        */

        $ambulance = Ambulance::findOrFail(
            $ambulanceId
        );

        if (
            $ambulance->status !== Ambulance::STATUS_AVAILABLE
        ) {

            return back()->with(
                'error',
                'This ambulance is currently not available.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Check active driver dispatch
        |--------------------------------------------------------------------------
        */

        if (
            Dispatch::active()
            ->where('driver_id', $driverId)
            ->exists()
        ) {

            return back()->with(
                'error',
                'This driver already has an active dispatch.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Check active ambulance dispatch
        |--------------------------------------------------------------------------
        */

        if (
            Dispatch::active()
            ->where('vehicle_id', $ambulanceId)
            ->exists()
        ) {

            return back()->with(
                'error',
                'This ambulance already has an active dispatch.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Check active incident dispatch
        |--------------------------------------------------------------------------
        */

        if (
            Dispatch::active()
            ->where('incident_id', $incident->id)
            ->exists()
        ) {

            return back()->with(
                'error',
                'This incident already has an active dispatch.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Create dispatch
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $incident,
            $driverId,
            $ambulanceId
        ) {

            Dispatch::create([
                'incident_id' => $incident->id,
                'driver_id' => $driverId,
                'vehicle_id' => $ambulanceId,
                'status' => Dispatch::STATUS_ASSIGNED,
                'assigned_at' => now(),
            ]);


            /*
            |--------------------------------------------------------------------------
            | Update incident
            |--------------------------------------------------------------------------
            */

            $incident->update([
                'driver_id' => $driverId,
                'ambulance_id' => $ambulanceId,
                'status' => Incident::STATUS_DISPATCHED,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Update driver
            |--------------------------------------------------------------------------
            */

            $driver = Driver::findOrFail(
                $driverId
            );

            $driver->update([
                'status' => Driver::STATUS_EN_ROUTE,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Update ambulance
            |--------------------------------------------------------------------------
            */

            $ambulance = Ambulance::findOrFail(
                $ambulanceId
            );

            $ambulance->update([
                'status' => Ambulance::STATUS_ON_DUTY,
            ]);
        });


        return back()->with(
            'success',
            'Dispatch assigned successfully.'
        );
    }
}
