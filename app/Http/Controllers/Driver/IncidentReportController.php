<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Models\Incident;
use App\Models\IncidentReport;
use App\Models\Notification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IncidentReportController extends Controller
{
    public function create(?Incident $incident = null)
    {
        $incident = $this->resolveIncident($incident, false);

        return view('driver.reports.create', compact('incident'));
    }

    public function store(
        Request $request,
        ?Incident $incident = null
    ) {
        $incident = $this->resolveIncident($incident);
        $driver = Auth::user()?->driver;

        abort_if(!$driver || !$incident, 403);

        $data = $request->validate([
            'summary' => ['required', 'string', 'max:2000'],
            'actions_taken' => ['required', 'string', 'max:2000'],
            'casualties' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($incident, $driver, $data) {
            abort_if($incident->report()->exists(), 409, 'A report has already been submitted.');

            IncidentReport::create([
                'incident_id' => $incident->id,
                'driver_id' => $driver->id,
                'summary' => $data['summary'],
                'actions_taken' => $data['actions_taken'],
                'casualties' => $data['casualties'],
                'remarks' => $data['remarks'] ?? null,
                'submitted_at' => now(),
                'status' => 'pending',
            ]);

            $dispatch = $incident->dispatches()
                ->where('driver_id', $driver->id)
                ->where('status', Dispatch::STATUS_COMPLETED)
                ->latest('completed_at')
                ->first();

            if ($dispatch) {
                $dispatch->update([
                    'status' => Dispatch::STATUS_CLOSED,
                ]);
            }

            $incident->update([
                'status' => Incident::STATUS_CLOSED,
            ]);

            $hasOtherActiveDispatch = Dispatch::where('driver_id', $driver->id)
                ->whereNotIn('status', [
                    Dispatch::STATUS_COMPLETED,
                    Dispatch::STATUS_CLOSED,
                    Dispatch::STATUS_CANCELLED,
                ])
                ->exists();

            if (! $hasOtherActiveDispatch) {
                $driver->update([
                    'status' => \App\Models\Driver::STATUS_AVAILABLE,
                ]);
            }

            if ($incident->ambulance_id) {
                $hasOtherVehicleDispatch = Dispatch::where('vehicle_id', $incident->ambulance_id)
                    ->whereNotIn('status', [
                        Dispatch::STATUS_COMPLETED,
                        Dispatch::STATUS_CLOSED,
                        Dispatch::STATUS_CANCELLED,
                    ])
                    ->exists();

                if (! $hasOtherVehicleDispatch) {
                    $incident->ambulance?->update([
                        'status' => Ambulance::STATUS_AVAILABLE,
                    ]);
                }
            }

            Notification::create([
                'title' => 'New Incident Report',
                'message' => 'Driver submitted report for Incident #' . $incident->incident_number,
                'type' => 'report',
                'is_read' => false,
            ]);
        });

        return redirect()
            ->route('driver.dashboard')
            ->with(
                'success',
                'Incident report submitted successfully.'
            );
    }

    protected function resolveIncident(?Incident $incident, bool $abortOnMissing = true): ?Incident
    {
        $driver = Auth::user()?->driver;
        abort_if(!$driver, 403);

        if ($incident) {
            abort_if($incident->driver_id !== $driver->id, 403);
            abort_if($incident->status !== Incident::STATUS_COMPLETED, 403, 'Incident is not ready for reporting.');
            abort_if($incident->report()->exists(), 409, 'A report has already been submitted.');

            return $incident;
        }

        $dispatch = Dispatch::with(['incident.report'])
            ->where('driver_id', $driver->id)
            ->where('status', Dispatch::STATUS_COMPLETED)
            ->whereDoesntHave('incident.report')
            ->latest('completed_at')
            ->first();

        if (!$dispatch || !$dispatch->incident) {
            if ($abortOnMissing) {
                abort(404, 'No reportable incident found.');
            }

            return null;
        }

        return $dispatch->incident;
    }
}
