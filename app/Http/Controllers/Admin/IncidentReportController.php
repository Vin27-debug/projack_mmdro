<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\IncidentReport;
use App\Models\Notification;
use App\Services\AuditService;
use Illuminate\Http\Request;

class IncidentReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:30'],
        ]);

        $search = trim($filters['search'] ?? '');
        $status = $filters['status'] ?? '';

        $reports = IncidentReport::query()
            ->with(['incident', 'driver.user'])
            ->when($status !== '', fn($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('summary', 'like', '%' . $search . '%')
                        ->orWhere('actions_taken', 'like', '%' . $search . '%')
                        ->orWhere('casualties', 'like', '%' . $search . '%')
                        ->orWhere('remarks', 'like', '%' . $search . '%')
                        ->orWhereHas('incident', fn($incident) => $incident->where('incident_number', 'like', '%' . $search . '%'))
                        ->orWhereHas('driver.user', fn($user) => $user->where('name', 'like', '%' . $search . '%'));
                });
            })
            ->latest('submitted_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.index', compact('reports', 'search', 'status'));
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

        $dispatch = $report->incident->dispatches()->latest()->first();
        if ($dispatch && $dispatch->status !== Dispatch::STATUS_COMPLETED) {
            $oldStatus = $dispatch->status;
            $dispatch->update([
                'status' => Dispatch::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
            AuditService::logDispatch($dispatch, 'dispatch_status_changed', $oldStatus);
        }

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
