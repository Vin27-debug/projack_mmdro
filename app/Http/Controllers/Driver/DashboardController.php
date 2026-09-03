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
                    'call_received_at' => $dispatch->incident->call_received_at ?? now(),
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
                'declined_at' => now(),
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
     * Mark incident as call received.
     */
    public function markCallReceived(
        Incident $incident
    ): RedirectResponse {

        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        $dispatch = $this->getDriverDispatchForIncident($incident, $driver);

        if (!$dispatch) {
            abort(403, 'Dispatch is not available for this incident.');
        }

        if ($incident->call_received_at) {
            return back()->with('error', 'Call received time has already been recorded.');
        }

        $incident->update([
            'call_received_at' => now(),
            'status' => Incident::STATUS_DISPATCHED,
        ]);

        return back()->with('success', 'Call received time recorded.');
    }

    /**
     * Mark incident response start.
     */
    public function markResponse(
        Incident $incident
    ): RedirectResponse {

        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        $dispatch = $this->getDriverDispatchForIncident($incident, $driver, [Dispatch::STATUS_ACCEPTED]);

        if (!$dispatch) {
            abort(403, 'Dispatch is not eligible to be marked as responding.');
        }

        if ($incident->response_at) {
            return back()->with('error', 'Response time has already been recorded.');
        }

        DB::transaction(function () use ($incident, $dispatch, $driver) {
            $incident->update([
                'call_received_at' => $incident->call_received_at ?? now(),
                'response_at' => now(),
                'status' => Incident::STATUS_DISPATCHED,
            ]);

            $dispatch->update([
                'status' => Dispatch::STATUS_ACCEPTED,
                'accepted_at' => $dispatch->accepted_at ?? now(),
            ]);

            $driver->update([
                'status' => Driver::STATUS_ASSIGNED,
            ]);
        });

        return back()->with('success', 'Response time recorded.');
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

        $dispatch = $this->getDriverDispatchForIncident($incident, $driver, [Dispatch::STATUS_ACCEPTED]);

        if (!$dispatch) {
            abort(403, 'Dispatch is not eligible to be marked en route.');
        }

        if ($incident->response_at === null) {
            return back()->with('error', 'Response time must be recorded before en route.');
        }

        DB::transaction(function () use ($incident, $dispatch, $driver) {
            $incident->update([
                'status' => Incident::STATUS_DISPATCHED,
            ]);

            $dispatch->update([
                'status' => Dispatch::STATUS_EN_ROUTE,
                'en_route_at' => now(),
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

        return back()->with('success', 'Incident marked as en route.');
    }


    /**
     * Mark incident at scene.
     */
    public function markAtScene(
        Incident $incident
    ): RedirectResponse {

        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        $dispatch = $this->getDriverDispatchForIncident($incident, $driver, [Dispatch::STATUS_EN_ROUTE]);

        if (!$dispatch) {
            abort(403, 'Dispatch is not eligible to be marked at scene.');
        }

        if ($incident->response_at === null) {
            return back()->with('error', 'Response time must be recorded before at scene.');
        }

        if ($incident->at_scene_at) {
            return back()->with('error', 'At scene time has already been recorded.');
        }

        DB::transaction(function () use ($incident, $dispatch, $driver) {
            $incident->update([
                'at_scene_at' => now(),
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

        return back()->with('success', 'Scene arrival time recorded.');
    }

    /**
     * Marks the patient at location.
     */
    public function markAtPatient(
        Incident $incident
    ): RedirectResponse {

        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        $dispatch = $this->getDriverDispatchForIncident($incident, $driver, [Dispatch::STATUS_ARRIVED]);

        if (!$dispatch) {
            abort(403, 'Dispatch is not eligible to record patient arrival.');
        }

        if ($incident->at_scene_at === null) {
            return back()->with('error', 'At scene time must be recorded before at patient.');
        }

        if ($incident->at_patient_at) {
            return back()->with('error', 'At patient time has already been recorded.');
        }

        $incident->update([
            'at_patient_at' => now(),
            'status' => Incident::STATUS_RESPONDING,
        ]);

        return back()->with('success', 'Patient arrival time recorded.');
    }

    /**
     * Marks departure from scene.
     */
    public function markDepartScene(
        Incident $incident
    ): RedirectResponse {

        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        $dispatch = $this->getDriverDispatchForIncident($incident, $driver, [Dispatch::STATUS_ARRIVED]);

        if (!$dispatch) {
            abort(403, 'Dispatch is not eligible to record scene departure.');
        }

        if ($incident->at_patient_at === null) {
            return back()->with('error', 'At patient time must be recorded before departing scene.');
        }

        if ($incident->depart_scene_at) {
            return back()->with('error', 'Depart scene time has already been recorded.');
        }

        $incident->update([
            'depart_scene_at' => now(),
            'status' => Incident::STATUS_RESPONDING,
        ]);

        return back()->with('success', 'Depart scene time recorded.');
    }

    /**
     * Marks arrival at the hospital.
     */
    public function markAtHospital(
        Incident $incident
    ): RedirectResponse {

        $driver = Auth::user()?->driver;

        if (!$driver) {
            abort(403, 'Driver profile not found.');
        }

        $dispatch = $this->getDriverDispatchForIncident($incident, $driver, [Dispatch::STATUS_ACCEPTED, Dispatch::STATUS_EN_ROUTE, Dispatch::STATUS_ARRIVED]);

        if (!$dispatch) {
            abort(403, 'Dispatch is not eligible to record hospital arrival.');
        }

        if ($incident->depart_scene_at === null) {
            return back()->with('error', 'Depart scene time must be recorded before hospital arrival.');
        }

        if ($incident->at_hospital_at) {
            return back()->with('error', 'At hospital time has already been recorded.');
        }

        $incident->update([
            'at_hospital_at' => now(),
        ]);

        return back()->with('success', 'At hospital time recorded.');
    }

    /**
     * Mark incident as ARRIVED
     */
    public function markArrived(
        Incident $incident
    ): RedirectResponse {

        return $this->markAtScene($incident);
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

        $dispatch = $this->getDriverDispatchForIncident($incident, $driver, [Dispatch::STATUS_ARRIVED]);

        if (!$dispatch) {
            abort(403, 'Dispatch is not eligible to complete this incident.');
        }

        if ($incident->at_hospital_at === null) {
            return back()->with('error', 'At hospital time must be recorded before completing the incident.');
        }

        if ($incident->completed_at) {
            return back()->with('error', 'Incident has already been completed.');
        }

        DB::transaction(function () use ($incident, $dispatch, $driver) {
            $incident->update([
                'status' => Incident::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            $dispatch->update([
                'status' => Dispatch::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            $driver->update([
                'status' => Driver::STATUS_RETURNING,
            ]);

            if ($dispatch->vehicle) {
                $dispatch->vehicle->update([
                    'status' => Ambulance::STATUS_AVAILABLE,
                ]);
            }
        });

        return back()->with('success', 'Incident completed successfully.');
    }


    protected function getDriverDispatchForIncident(Incident $incident, $driver, array $statuses = null): ?Dispatch
    {
        $query = Dispatch::where('incident_id', $incident->id)
            ->where('driver_id', $driver->id)
            ->latest('assigned_at');

        if (is_array($statuses) && !empty($statuses)) {
            $query->whereIn('status', $statuses);
        }

        return $query->first();
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
