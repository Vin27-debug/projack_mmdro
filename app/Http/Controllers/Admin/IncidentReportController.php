<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\IncidentReport;
use App\Models\Notification;
use App\Services\AuditService;

class IncidentReportController extends Controller
{
    public function index()
    {


        $reports = IncidentReport::with([
            'incident',
            'driver.user'
        ])
            ->latest()
            ->get();

        return view(
            'admin.reports.index',
            compact('reports')
        );
    }

    public function approve(IncidentReport $report)
    {
        $report->update([
            'status' => 'approved'
        ]);

        $report->incident->update([
            'status' => 'closed',
            'closed_at' => now()
        ]);

        $report->incident->dispatches()->latest()->first()?->update([
            'status' => Dispatch::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $driver = $report->driver;

        if ($driver) {
            $driver->update([
                'status' => 'available'
            ]);
        }

        if ($report->incident->ambulance) {
            $report->incident->ambulance->update([
                'status' => 'available'
            ]);
        }

        Notification::create([
            'user_id' => $driver?->user_id,
            'title' => 'Incident Report Approved',
            'message' => 'Your report for incident #' . $report->incident->incident_number . ' was approved and the incident was closed.',
            'type' => 'report',
            'is_read' => false,
        ]);

        AuditService::log(
            'Approve Report',
            'Incident Report',
            'Approved report #' . $report->id
        );

        return back()->with(
            'success',
            'Report approved.'
        );
    }
}
