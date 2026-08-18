<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Incident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $driver = Auth::user()?->driver()->with([
            'activeVehicleAssignment.ambulance',
            'activeDispatch.incident',
            'activeDispatch.ambulance',
        ])->first();

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        $currentDispatch = $driver->activeDispatch()->with(['incident', 'vehicle', 'ambulance'])->first();

        $reportableDispatch = Dispatch::with('incident')
            ->where('driver_id', $driver->id)
            ->where('status', Dispatch::STATUS_COMPLETED)
            ->whereHas('incident', function ($query) {
                $query->where('status', Incident::STATUS_COMPLETED);
            })
            ->whereDoesntHave('incident.report')
            ->latest('completed_at')
            ->first();

        $incidents = Incident::with(['ambulance'])
            ->where('driver_id', $driver->id)
            ->latest()
            ->get();

        return view(
            'driver.dashboard',
            compact(
                'driver',
                'currentDispatch',
                'reportableDispatch',
                'incidents'
            )
        );
    }

    public function acceptDispatch(Dispatch $dispatch): RedirectResponse
    {


        try {

            $driver = Auth::user()->driver;

            DB::transaction(function () use ($dispatch, $driver) {

                $dispatch->update([
                    'status' => Dispatch::STATUS_ACCEPTED,
                    'accepted_at' => now(),
                ]);

                $dispatch->incident->update([
                    'status' => Incident::STATUS_DISPATCHED,
                ]);

                $driver->update([
                    'status' => Driver::STATUS_EN_ROUTE,
                ]);

                $dispatch->vehicle?->update([
                    'status' => Ambulance::STATUS_ON_DUTY,
                ]);
            });

            return back()->with('success', 'Accepted');
        } catch (\Throwable $e) {
            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }
    public function declineDispatch(Dispatch $dispatch): RedirectResponse
    {
        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        // Make sure this dispatch belongs to the logged-in driver
        if ((int) $dispatch->driver_id !== (int) $driver->id) {
            abort(403, 'You are not authorized to decline this dispatch.');
        }

        // Don't allow declining an already finished dispatch
        if (in_array($dispatch->status, [
            Dispatch::STATUS_COMPLETED,
            Dispatch::STATUS_CLOSED,
            Dispatch::STATUS_CANCELLED,
        ], true)) {
            return back()->with(
                'error',
                'This dispatch is already closed.'
            );
        }

        DB::transaction(function () use ($dispatch, $driver) {

            /*
        |--------------------------------------------------------------------------
        | 1. Cancel the dispatch only
        |--------------------------------------------------------------------------
        */

            $dispatch->update([
                'status' => Dispatch::STATUS_CANCELLED,
            ]);


            /*
        |--------------------------------------------------------------------------
        | 2. Return incident to pending
        |--------------------------------------------------------------------------
        |
        | The incident is NOT cancelled.
        | It can still be assigned to another driver.
        |
        */

            if ($dispatch->incident) {

                $dispatch->incident->update([
                    'status' => Incident::STATUS_PENDING,
                    'driver_id' => null,
                    'ambulance_id' => null,
                ]);
            }


            /*
        |--------------------------------------------------------------------------
        | 3. Make ambulance available
        |--------------------------------------------------------------------------
        */

            if ($dispatch->vehicle) {

                $dispatch->vehicle->update([
                    'status' => Ambulance::STATUS_AVAILABLE,
                ]);
            }


            /*
        |--------------------------------------------------------------------------
        | 4. Make driver available
        |--------------------------------------------------------------------------
        */

            $hasActiveDispatch = Dispatch::where(
                'driver_id',
                $driver->id
            )
                ->whereNotIn('status', [
                    Dispatch::STATUS_COMPLETED,
                    Dispatch::STATUS_CLOSED,
                    Dispatch::STATUS_CANCELLED,
                ])
                ->exists();


            if (!$hasActiveDispatch) {

                $driver->update([
                    'status' => Driver::STATUS_AVAILABLE,
                ]);
            }
        });


        return back()->with(
            'success',
            'Assignment declined. The incident is now available for reassignment.'
        );
    }

    public function markEnRoute(Incident $incident): RedirectResponse
    {
        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        $dispatch = Dispatch::where('incident_id', $incident->id)
            ->where('driver_id', $driver->id)
            ->whereIn('status', [
                Dispatch::STATUS_ACCEPTED,
                Dispatch::STATUS_ASSIGNED,
            ])
            ->latest()
            ->first();

        if (!$dispatch) {
            abort(403, 'Dispatch is not eligible to be marked en route.');
        }

        DB::transaction(function () use ($incident, $dispatch, $driver) {
            $incident->update([
                'status' => Incident::STATUS_DISPATCHED,
            ]);

            $dispatch->update([
                'status' => Dispatch::STATUS_EN_ROUTE,
                'accepted_at' => $dispatch->accepted_at ?? now(),
            ]);

            $driver->update([
                'status' => Driver::STATUS_EN_ROUTE,
            ]);

            $dispatch->vehicle?->update([
                'status' => Ambulance::STATUS_ON_DUTY,
            ]);
        });

        return back()->with(
            'success',
            'Incident marked as en route.'
        );
    }

    public function markArrived(Incident $incident): RedirectResponse
    {
        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        $dispatch = Dispatch::where('incident_id', $incident->id)
            ->where('driver_id', $driver->id)
            ->whereIn('status', [
                Dispatch::STATUS_ACCEPTED,
                Dispatch::STATUS_EN_ROUTE,
            ])
            ->latest()
            ->first();

        if (!$dispatch) {
            abort(403, 'Dispatch is not eligible to be marked arrived.');
        }

        DB::transaction(function () use ($incident, $dispatch, $driver) {
            $incident->update([
                'status' => Incident::STATUS_RESPONDING,
            ]);

            $dispatch->update([
                'status' => Dispatch::STATUS_ARRIVED,
                'arrived_at' => now(),
            ]);

            $driver->update([
                'status' => Driver::STATUS_ON_SCENE,
            ]);
        });

        return back()->with(
            'success',
            'Incident marked as on scene.'
        );
    }

    public function markCompleted(Incident $incident): RedirectResponse
    {
        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        $dispatch = Dispatch::where('incident_id', $incident->id)
            ->where('driver_id', $driver->id)
            ->whereIn('status', [
                Dispatch::STATUS_ACCEPTED,
                Dispatch::STATUS_EN_ROUTE,
                Dispatch::STATUS_ARRIVED,
            ])
            ->latest('assigned_at')
            ->first();

        if (!$dispatch) {
            abort(403, 'Dispatch is not eligible to be completed.');
        }

        DB::transaction(function () use ($incident, $dispatch, $driver) {
            $incident->update([
                'status' => Incident::STATUS_COMPLETED,
            ]);

            Dispatch::active()
                ->where('incident_id', $incident->id)
                ->update([
                    'status' => Dispatch::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);

            $driver->update([
                'status' => Driver::STATUS_RETURNING,
            ]);

            if ($dispatch->vehicle && ! Dispatch::active()->where('vehicle_id', $dispatch->vehicle->id)->exists()) {
                $dispatch->vehicle->update([
                    'status' => Ambulance::STATUS_AVAILABLE,
                ]);
            }
        });

        return back()->with(
            'success',
            'Incident marked as completed.'
        );
    }

    protected function authorizeIncident(Incident $incident): void
    {
        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403);
        }

        $assigned = Dispatch::where('incident_id', $incident->id)
            ->where('driver_id', $driver->id)
            ->whereNotIn('status', [Dispatch::STATUS_CLOSED, Dispatch::STATUS_CANCELLED])
            ->exists();

        if (!$assigned) {
            abort(403);
        }
    }
}
