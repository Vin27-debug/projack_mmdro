<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Dispatch;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public static function log(
        $action,
        $module,
        $description = null
    ) {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }

    public static function logDispatch(
        Dispatch $dispatch,
        string $action,
        ?string $oldStatus = null
    ): void {
        $dispatch->loadMissing(['incident', 'vehicle', 'driver.user']);

        $vehicle = $dispatch->vehicle;
        $vehicleName = $vehicle?->vehicle_name ?? $vehicle?->plate_number ?? 'Unassigned vehicle';
        $vehicleLabel = $vehicle?->plate_number
            ? $vehicleName . ' (' . $vehicle->plate_number . ')'
            : $vehicleName;
        $driverName = $dispatch->driver?->user?->name ?? 'Unassigned driver';
        $actorName = Auth::user()?->name ?? 'System';
        $incidentId = $dispatch->incident?->id ?? $dispatch->incident_id;
        $statusChange = $oldStatus !== null && $oldStatus !== $dispatch->status
            ? sprintf(
                ' Status changed from %s to %s.',
                str_replace('_', ' ', $oldStatus),
                str_replace('_', ' ', $dispatch->status)
            )
            : '';

        $event = match ($action) {
            'dispatch_assigned' => sprintf(
                'Assigned %s and driver %s to Incident #%s (Dispatch #%s).',
                $vehicleLabel,
                $driverName,
                $incidentId,
                $dispatch->id
            ),
            'dispatch_accepted' => sprintf(
                'Driver %s accepted Dispatch #%s for Incident #%s.%s',
                $driverName,
                $dispatch->id,
                $incidentId,
                $statusChange
            ),
            'dispatch_declined' => sprintf(
                'Driver %s declined Dispatch #%s for Incident #%s.%s',
                $driverName,
                $dispatch->id,
                $incidentId,
                $statusChange
            ),
            default => sprintf(
                'Dispatch #%s for Incident #%s changed status.%s',
                $dispatch->id,
                $incidentId,
                $statusChange
            ),
        };
        $context = $action === 'dispatch_assigned'
            ? sprintf(' Acting user: %s.', $actorName)
            : sprintf(
                ' Vehicle: %s. Driver: %s. Acting user: %s.',
                $vehicleLabel,
                $driverName,
                $actorName
            );

        self::log(
            $action,
            'Dispatch',
            $event . $context
        );
    }
}
