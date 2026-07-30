<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Driver;
use App\Models\Ambulance;
use App\Models\PanicAlert;
use App\Models\HijackAlert;
use App\Models\IncidentReport;
use App\Models\Dispatch;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\GpsLocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {



        $monthExpression = DB::getDriverName() === 'sqlite'
            ? "strftime('%b', created_at)"
            : "DATE_FORMAT(created_at, '%b')";

        $incidentStats = Incident::query()
            ->selectRaw($monthExpression . ' as month')
            ->selectRaw('COUNT(*) as count')
            ->whereNotNull('created_at')
            ->groupBy('month')
            ->orderByRaw('MIN(created_at)')
            ->get()
            ->pluck('count', 'month');

        $incidentList = Incident::latest()->get();
        $ambulanceList = Ambulance::latest()->get();

        $totalIncidents = Incident::query()->count();
        $activeIncidents = Incident::query()->whereNotIn('status', ['completed', 'closed', 'cancelled'])->count();
        $completedIncidents = Incident::query()->whereIn('status', ['completed', 'closed'])->count();
        $closedIncidents = Incident::query()->where('status', 'closed')->count();

        $incidentChart = [
            'Active' => $activeIncidents,
            'Completed' => $completedIncidents,
        ];

        $totalDrivers = Driver::count();

        $availableDrivers = Driver::where(
            'status',
            'available'
        )->count();

        $availableVehicles = Ambulance::whereIn('status', ['available', 'ready', 'standby'])->count();

        $maintenanceVehicles = Ambulance::where(
            'status',
            'maintenance'
        )->count();

        $activeDispatches = Dispatch::whereNotIn('status', ['completed', 'cancelled'])->count();

        $completedDispatches = Dispatch::where(
            'status',
            'completed'
        )->count();

        $approvedReports = IncidentReport::where(
            'status',
            'approved'
        )->count();

        $submittedReports = IncidentReport::where(
            'status',
            'submitted'
        )->count();

        $submittedReports = IncidentReport::where(
            'status',
            'pending'
        )->count();

        $activePanicAlerts = PanicAlert::with(
            'driver.user'
        )
            ->where('status', 'active')
            ->latest()
            ->get();

        $panicCount = $activePanicAlerts->count();

        $activeHijackAlerts = HijackAlert::with(
            'driver.user'
        )
            ->where('status', 'active')
            ->latest()
            ->get();

        $averageResponseTime = Dispatch::query()
            ->whereNotNull('assigned_at')
            ->whereNotNull('arrived_at')
            ->get()
            ->map(function (Dispatch $dispatch): ?float {
                if (!$dispatch->assigned_at || !$dispatch->arrived_at) {
                    return null;
                }

                return $dispatch->assigned_at->diffInMinutes($dispatch->arrived_at);
            })
            ->filter()
            ->avg();

        $responseTime = (int) round($averageResponseTime ?? 0);

        $unreadNotifications = Notification::where(
            'is_read',
            false
        )->count();

        $recentNotifications = Notification::latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Dashboard Charts
        |--------------------------------------------------------------------------
        */

        $dispatchChart = [
            'Active' => $activeDispatches,
            'Completed' => $completedDispatches,
        ];

        /*
        |--------------------------------------------------------------------------
        | Recent Activities
        |--------------------------------------------------------------------------
        */

        $recentActivities = AuditLog::latest()
            ->take(10)
            ->get();

        // Detailed dispatch list for operations panel (preserve $activeDispatches as count)
        $dispatchList = Dispatch::with(['incident', 'vehicle', 'driver'])
            ->latest()
            ->get();

        // Collections for dashboard panels (separate from integer counts used in KPI cards)
        $incidents = Incident::with(['ambulance'])->latest()->take(20)->get();
        $ambulances = Ambulance::latest()->get();
        $drivers = Driver::latest()->get();
        $recentIncidents = Incident::latest()->take(8)->get();

        return view(
            'admin.dashboard',
            compact(
                'totalIncidents',
                'activeIncidents',
                'completedIncidents',
                'closedIncidents',
                'totalDrivers',
                'availableDrivers',
                'availableVehicles',
                'maintenanceVehicles',
                'activeDispatches',
                'completedDispatches',
                'approvedReports',
                'submittedReports',
                'activePanicAlerts',
                'activeHijackAlerts',
                'panicCount',
                'responseTime',
                'unreadNotifications',
                'incidentChart',
                'dispatchChart',
                'recentActivities',
                'recentNotifications',
                'dispatchList',
                'incidentList',
                'ambulanceList',
                'incidents',
                'ambulances',
                'drivers',
                'recentIncidents'
            )
        );
    }

    public function counters()
    {
        $responseTime = Dispatch::query()
            ->whereNotNull('assigned_at')
            ->whereNotNull('arrived_at')
            ->get()
            ->map(function (Dispatch $dispatch): ?float {
                if (!$dispatch->assigned_at || !$dispatch->arrived_at) {
                    return null;
                }

                return $dispatch->assigned_at->diffInMinutes($dispatch->arrived_at);
            })
            ->filter()
            ->avg();

        return response()->json([
            'totalIncidents' => Incident::query()->count(),
            'activeIncidents' => Incident::query()->whereNotIn('status', ['completed', 'closed', 'cancelled'])->count(),
            'completedIncidents' => Incident::query()->whereIn('status', ['completed', 'closed'])->count(),
            'closedIncidents' => Incident::query()->where('status', 'closed')->count(),
            'totalDrivers' => Driver::count(),
            'availableDrivers' => Driver::where('status', 'available')->count(),
            'availableVehicles' => Ambulance::whereIn('status', ['available', 'ready', 'standby'])->count(),
            'maintenanceVehicles' => Ambulance::where('status', 'maintenance')->count(),
            'activeDispatches' => Dispatch::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'completedDispatches' => Dispatch::where('status', 'completed')->count(),
            'panicCount' => PanicAlert::where('status', 'active')->count(),
            'responseTime' => (int) round($responseTime ?? 0),
        ]);
    }

    /**
     * Get live command map data for ambulances, incidents, and drivers.
     */
    public function gpsLocations()
    {
        $latestDriverLocations = GpsLocation::with('driver.user')
            ->latest('recorded_at')
            ->get()
            ->groupBy('driver_id')
            ->map(function ($group) {
                return $group->first();
            });

        $ambulances = $latestDriverLocations->map(function ($location) {
            $driver = $location?->driver;
            $assignment = $driver?->activeVehicleAssignment()->first();
            $ambulance = $assignment?->ambulance;

            if (!$ambulance || !$location?->latitude || !$location?->longitude) {
                return null;
            }

            $status = strtolower((string) ($ambulance->status ?? 'available'));
            $mapStatus = $this->resolveMapStatus($status, $driver?->status);

            return [
                'id' => $ambulance->id,
                'name' => $ambulance->vehicle_name ?? 'Ambulance',
                'plate_number' => $ambulance->plate_number,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'status' => $ambulance->status ?? 'available',
                'status_key' => $mapStatus,
                'driver_name' => $driver?->user?->name ?? 'Unassigned',
                'last_updated' => $location->recorded_at?->format('M d, Y H:i') ?? 'Unknown',
                'type' => 'ambulance',
            ];
        })->filter();

        $incidents = Incident::whereNotIn('status', ['completed', 'closed'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->get()
            ->map(function ($incident) {
                return [
                    'id' => $incident->id,
                    'incident_number' => $incident->incident_number,
                    'latitude' => (float) $incident->latitude,
                    'longitude' => (float) $incident->longitude,
                    'type' => $incident->incident_type,
                    'status' => $incident->status,
                    'status_key' => 'emergency',
                    'location' => $incident->location,
                    'driver_name' => $incident->driver?->user?->name ?? 'Unassigned',
                    'last_updated' => $incident->updated_at?->format('M d, Y H:i') ?? 'Unknown',
                    'type_label' => 'incident',
                ];
            });

        $drivers = $latestDriverLocations->map(function ($location) {
            $driver = $location?->driver;
            $assignment = $driver?->activeVehicleAssignment()->first();
            $ambulance = $assignment?->ambulance;

            if (!$driver || !$location?->latitude || !$location?->longitude) {
                return null;
            }

            $driverStatus = strtolower((string) ($driver->status ?? 'available'));
            $mapStatus = $this->resolveMapStatus($driverStatus, $driverStatus);

            return [
                'id' => $driver->id,
                'driver_name' => $driver->user?->name ?? 'Driver',
                'ambulance_unit' => $ambulance?->vehicle_name ?? 'Unassigned',
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'status' => $driver->status ?? 'available',
                'status_key' => $mapStatus,
                'last_updated' => $location->recorded_at?->format('M d, Y H:i') ?? 'Unknown',
                'type' => 'driver',
            ];
        })->filter();

        return response()->json([
            'ambulances' => $ambulances->values(),
            'incidents' => $incidents->values(),
            'drivers' => $drivers->values(),
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function liveCommandMapData()
    {
        return $this->gpsLocations();
    }

    protected function resolveMapStatus(string $status, ?string $fallback = null): string
    {
        $candidate = strtolower(trim($status ?: $fallback ?? 'available'));

        if (in_array($candidate, ['available', 'idle', 'ready', 'standby', 'offline'])) {
            return 'available';
        }

        if (in_array($candidate, ['en_route', 'dispatched', 'assigned', 'on_duty', 'responding', 'arrived'])) {
            return 'en_route';
        }

        if (in_array($candidate, ['emergency', 'panic', 'critical', 'alert', 'active'])) {
            return 'emergency';
        }

        return 'available';
    }

    /**
     * Get response load analytics data (incidents by type)
     */
    public function responseLoadAnalytics()
    {
        $incidents = Incident::select('incident_type')
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $grouped = collect($incidents)->groupBy('incident_type');

        $data = [
            'Fire' => $grouped->get('Fire')?->count() ?? 0,
            'Medical' => $grouped->get('Medical')?->count() ?? 0,
            'Rescue' => $grouped->get('Rescue')?->count() ?? 0,
            'Crime' => $grouped->get('Crime')?->count() ?? 0,
        ];

        return response()->json($data);
    }

    /**
     * Get situation overview data
     */
    public function situationOverview()
    {
        return response()->json([
            'active_incidents' => Incident::whereNotIn('status', ['completed', 'closed'])->count(),
            'dispatched_units' => Dispatch::where('status', '!=', 'completed')->count(),
            'completed_responses' => Dispatch::where('status', 'completed')->count(),
            'pending_reports' => IncidentReport::where('status', '!=', 'approved')->count(),
        ]);
    }

    /**
     * Get fleet readiness data
     */
    public function fleetReadiness()
    {
        $onlineDrivers = Driver::where('status', 'available')->count();
        $activeAmbulances = Ambulance::whereIn('status', ['available', 'in_use'])->count();
        $underMaintenance = Ambulance::where('status', 'maintenance')->count();
        $totalAmbulances = Ambulance::count();
        $availableAmbulances = Ambulance::where('status', 'available')->count();

        return response()->json([
            'available_ambulances' => $availableAmbulances,
            'active_ambulances' => $activeAmbulances,
            'vehicles_maintenance' => $underMaintenance,
            'drivers_online' => $onlineDrivers,
            'fleet_utilization' => $totalAmbulances > 0 ? round(($activeAmbulances / $totalAmbulances) * 100, 2) : 0,
        ]);
    }
}
