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
    /**
     * Driver Dashboard
     */
    public function index()
    {
        $user = Auth::user();

        $driver = $user?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        /*
        |--------------------------------------------------------------------------
        | Get driver's currently active dispatch
        |--------------------------------------------------------------------------
        |
        | Do NOT rely on the Driver::activeDispatch() relationship here.
        | We directly check the dispatch status so ASSIGNED is included.
        |
        */

        $currentDispatch = Dispatch::with([
            'incident',
            'driver.user',
            'vehicle',
        ])
            ->where('driver_id', $driver->id)
            ->whereNotIn('status', [
                Dispatch::STATUS_COMPLETED,
                Dispatch::STATUS_CLOSED,
                Dispatch::STATUS_CANCELLED,
            ])
            ->latest('assigned_at')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Completed dispatch that still needs an incident report
        |--------------------------------------------------------------------------
        */

        $reportableDispatch = Dispatch::with('incident')
            ->where('driver_id', $driver->id)
            ->where('status', Dispatch::STATUS_COMPLETED)
            ->whereHas('incident', function ($query) {
                $query->where('status', Incident::STATUS_COMPLETED);
            })
            ->whereDoesntHave('incident.report')
            ->latest('completed_at')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Driver's incidents
        |--------------------------------------------------------------------------
        */

        $incidents = Incident::with([
            'ambulance',
            'dispatches',
        ])
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


    /**
     * Accept Dispatch
     */
    public function acceptDispatch(
        Dispatch $dispatch
    ): RedirectResponse {

        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        */

        if ((int) $dispatch->driver_id !== (int) $driver->id) {
            abort(
                403,
                'You are not authorized to accept this dispatch.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Only ASSIGNED dispatches can be accepted
        |--------------------------------------------------------------------------
        */

        if ($dispatch->status !== Dispatch::STATUS_ASSIGNED) {
            return back()->with(
                'error',
                'This dispatch is no longer waiting for acceptance.'
            );
        }

        DB::transaction(function () use (
            $dispatch,
            $driver
        ) {

            /*
            |------------------------------------------------------------------
            | Dispatch
            |------------------------------------------------------------------
            */

            $dispatch->update([
                'status' => Dispatch::STATUS_ACCEPTED,
                'accepted_at' => now(),
            ]);

            /*
            |------------------------------------------------------------------
            | Incident
            |------------------------------------------------------------------
            */

            if ($dispatch->incident) {
                $dispatch->incident->update([
                    'status' => Incident::STATUS_DISPATCHED,
                ]);
            }

            /*
            |------------------------------------------------------------------
            | Driver
            |------------------------------------------------------------------
            */

            $driver->update([
                'status' => Driver::STATUS_ASSIGNED,
            ]);

            /*
            |------------------------------------------------------------------
            | Ambulance
            |------------------------------------------------------------------
            */

            if ($dispatch->vehicle) {
                $dispatch->vehicle->update([
                    'status' => Ambulance::STATUS_ON_DUTY,
                ]);
            }
        });

        return back()->with(
            'success',
            'Dispatch accepted successfully.'
        );
    }


    /**
     * Decline Dispatch
     */
    public function declineDispatch(
        Dispatch $dispatch
    ): RedirectResponse {

        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        */

        if ((int) $dispatch->driver_id !== (int) $driver->id) {
            abort(
                403,
                'You are not authorized to decline this dispatch.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Only active dispatches can be declined
        |--------------------------------------------------------------------------
        */

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

        DB::transaction(function () use (
            $dispatch,
            $driver
        ) {

            /*
            |------------------------------------------------------------------
            | 1. Cancel dispatch
            |------------------------------------------------------------------
            */

            $dispatch->update([
                'status' => Dispatch::STATUS_CANCELLED,
            ]);

            /*
            |------------------------------------------------------------------
            | 2. Return incident to pending
            |------------------------------------------------------------------
            */

            if ($dispatch->incident) {

                $dispatch->incident->update([
                    'status' => Incident::STATUS_PENDING,
                    'driver_id' => null,
                    'ambulance_id' => null,
                ]);
            }

            /*
            |------------------------------------------------------------------
            | 3. Make ambulance available
            |------------------------------------------------------------------
            */

            if ($dispatch->vehicle) {

                $dispatch->vehicle->update([
                    'status' => Ambulance::STATUS_AVAILABLE,
                ]);
            }

            /*
            |------------------------------------------------------------------
            | 4. Check if driver still has another active dispatch
            |------------------------------------------------------------------
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

            /*
            |------------------------------------------------------------------
            | 5. If no other assignment, make driver available
            |------------------------------------------------------------------
            */

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


    /**
     * Mark incident as EN ROUTE
     */
    public function markEnRoute(
        Incident $incident
    ): RedirectResponse {

        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        $dispatch = Dispatch::where(
            'incident_id',
            $incident->id
        )
            ->where(
                'driver_id',
                $driver->id
            )
            ->where(
                'status',
                Dispatch::STATUS_ACCEPTED
            )
            ->latest('accepted_at')
            ->first();

        if (!$dispatch) {
            abort(
                403,
                'Dispatch is not eligible to be marked en route.'
            );
        }

        DB::transaction(function () use (
            $incident,
            $dispatch,
            $driver
        ) {

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

            if ($dispatch->vehicle) {
                $dispatch->vehicle->update([
                    'status' => Ambulance::STATUS_ON_DUTY,
                ]);
            }
        });

        return back()->with(
            'success',
            'Incident marked as en route.'
        );
    }


    /**
     * Mark incident as ARRIVED
     */
    public function markArrived(
        Incident $incident
    ): RedirectResponse {

        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        $dispatch = Dispatch::where(
            'incident_id',
            $incident->id
        )
            ->where(
                'driver_id',
                $driver->id
            )
            ->where(
                'status',
                Dispatch::STATUS_EN_ROUTE
            )
            ->latest('accepted_at')
            ->first();

        if (!$dispatch) {
            abort(
                403,
                'Dispatch is not eligible to be marked arrived.'
            );
        }

        DB::transaction(function () use (
            $incident,
            $dispatch,
            $driver
        ) {

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

            if ($dispatch->vehicle) {
                $dispatch->vehicle->update([
                    'status' => Ambulance::STATUS_ON_DUTY,
                ]);
            }
        });

        return back()->with(
            'success',
            'Incident marked as on scene.'
        );
    }


    /**
     * Mark incident as COMPLETED
     */
    public function markCompleted(
        Incident $incident
    ): RedirectResponse {

        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        $dispatch = Dispatch::where(
            'incident_id',
            $incident->id
        )
            ->where(
                'driver_id',
                $driver->id
            )
            ->where(
                'status',
                Dispatch::STATUS_ARRIVED
            )
            ->latest('assigned_at')
            ->first();

        if (!$dispatch) {
            abort(
                403,
                'Dispatch is not eligible to be completed.'
            );
        }

        DB::transaction(function () use (
            $incident,
            $dispatch,
            $driver
        ) {

            /*
            |------------------------------------------------------------------
            | 1. Complete incident
            |------------------------------------------------------------------
            */

            $incident->update([
                'status' => Incident::STATUS_COMPLETED,
            ]);

            /*
            |------------------------------------------------------------------
            | 2. Complete dispatch
            |------------------------------------------------------------------
            */

            $dispatch->update([
                'status' => Dispatch::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            /*
            |------------------------------------------------------------------
            | 3. Driver returning
            |------------------------------------------------------------------
            */

            $driver->update([
                'status' => Driver::STATUS_RETURNING,
            ]);

            /*
            |------------------------------------------------------------------
            | 4. Ambulance available
            |------------------------------------------------------------------
            */

            if ($dispatch->vehicle) {

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


    /**
     * Authorize driver access to an incident
     */
    protected function authorizeIncident(
        Incident $incident
    ): void {

        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        $assigned = Dispatch::where(
            'incident_id',
            $incident->id
        )
            ->where(
                'driver_id',
                $driver->id
            )
            ->whereNotIn('status', [
                Dispatch::STATUS_CLOSED,
                Dispatch::STATUS_CANCELLED,
            ])
            ->exists();

        if (!$assigned) {
            abort(403);
        }
    }
}
