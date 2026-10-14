<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Driver;
use App\Models\Ambulance;
use App\Models\PanicAlert;
use App\Models\IncidentReport;
use App\Models\Dispatch;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\GpsLocation;
use App\Models\VulnerableArea;
use App\Services\GpsFreshnessService;
use App\Services\DispatchRecommendationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(DispatchRecommendationService $dispatchRecommendations)
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
        $totalFleet = Ambulance::notArchived()->count();

        $totalIncidents = Incident::query()->notArchived()->count();
        $activeIncidents = Incident::query()->notArchived()->open()->count();
        $priorityIncidents = Incident::query()->notArchived()->open()
            ->whereIn('priority', [Incident::PRIORITY_HIGH, Incident::PRIORITY_CRITICAL])
            ->count();
        $incidentStatusCounts = Incident::query()
            ->notArchived()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $completedIncidents = Incident::query()->notArchived()->where('status', Incident::STATUS_COMPLETED)->count();
        $closedIncidents = Incident::query()->notArchived()->where('status', Incident::STATUS_CLOSED)->count();

        $incidentChart = [
            'Active' => $activeIncidents,
            'Completed' => $completedIncidents,
        ];

        $totalDrivers = Driver::notArchived()->count();

        $availableDrivers = $dispatchRecommendations->eligibleDrivers()->count();

        $availableVehicles = Ambulance::available()->count();
        $respondingVehicles = Ambulance::notArchived()
            ->where('status', '!=', Ambulance::STATUS_MAINTENANCE)
            ->where(function ($query): void {
                $query->where('status', Ambulance::STATUS_ON_DUTY)
                    ->orWhereHas('dispatches', fn($dispatches) => $dispatches
                        ->active()
                        ->whereHas('incident', fn($incident) => $incident->notArchived()));
            })
            ->count();

        $maintenanceVehicles = Ambulance::notArchived()->where(
            'status',
            'maintenance'
        )->count();

        $activeDispatches = Dispatch::active()
            ->whereHas('incident', fn($query) => $query->notArchived())
            ->count();

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
            ->where('resolved', false)
            ->latest()
            ->get();

        $panicCount = $activePanicAlerts->count();

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

        $unreadNotifications = Notification::visibleTo(Auth::id())
            ->where('is_read', false)
            ->count();

        $recentNotifications = Notification::visibleTo(Auth::id())->latest()
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
        $dispatchList = Dispatch::active()
            ->whereHas('incident', fn($query) => $query->notArchived())
            ->with(['incident', 'vehicle', 'driver.user'])
            ->latest('updated_at')
            ->take(6)
            ->get();

        // Collections for dashboard panels (separate from integer counts used in KPI cards)
        $incidents = Incident::with(['ambulance'])->latest()->take(20)->get();
        $ambulances = Ambulance::latest()->get();
        $drivers = Driver::latest()->get();
        $recentIncidents = Incident::notArchived()->open()->latest()->take(8)->get();
        $priorityAdvisories = Incident::query()
            ->with(['dispatches' => fn($query) => $query->active()->latest('created_at')])
            ->notArchived()
            ->open()
            ->whereIn('priority', [Incident::PRIORITY_CRITICAL, Incident::PRIORITY_HIGH])
            ->orderByRaw("CASE priority WHEN 'Critical' THEN 0 WHEN 'High' THEN 1 ELSE 2 END")
            ->latest('created_at')
            ->take(6)
            ->get();

        $stats = [
            'total_incidents' => $totalIncidents,
            'pending_incidents' => (int) ($incidentStatusCounts[Incident::STATUS_PENDING] ?? 0),
            'dispatched_incidents' => (int) ($incidentStatusCounts[Incident::STATUS_DISPATCHED] ?? 0),
            'completed_incidents' => $completedIncidents,
            'total_drivers' => $totalDrivers,
            'available_ambulances' => $availableVehicles,
        ];

        return view(
            $this->dashboardView(),
            compact(
                'totalIncidents',
                'activeIncidents',
                'priorityIncidents',
                'incidentStatusCounts',
                'completedIncidents',
                'closedIncidents',
                'totalDrivers',
                'availableDrivers',
                'totalFleet',
                'availableVehicles',
                'respondingVehicles',
                'maintenanceVehicles',
                'activeDispatches',
                'completedDispatches',
                'approvedReports',
                'submittedReports',
                'activePanicAlerts',
                'panicCount',
                'responseTime',
                'unreadNotifications',
                'incidentChart',
                'dispatchChart',
                'recentActivities',
                'recentNotifications',
                'dispatchList',
                'incidentList',
                'incidents',
                'ambulances',
                'drivers',
                'recentIncidents',
                'priorityAdvisories',
                'stats'
            )
        );
    }

    protected function dashboardView(): string
    {
        return 'admin.dashboard';
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
            'totalDrivers' => Driver::notArchived()->count(),
            'availableDrivers' => Driver::dispatchEligible()->count(),
            'availableVehicles' => Ambulance::notArchived()->whereIn('status', ['available', 'ready', 'standby'])->count(),
            'maintenanceVehicles' => Ambulance::notArchived()->where('status', 'maintenance')->count(),
            'activeDispatches' => Dispatch::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'completedDispatches' => Dispatch::where('status', 'completed')->count(),
            'panicCount' => PanicAlert::where('resolved', false)->count(),
            'responseTime' => (int) round($responseTime ?? 0),
        ]);
    }

    /**
     * Get live command map data for ambulances, incidents, and drivers.
     */
    public function gpsLocations(GpsFreshnessService $gpsFreshness)
    {
        // Get latest GPS location per driver more efficiently using subquery
        $latestDriverLocations = GpsLocation::with('driver.user')
            ->whereHas('driver', fn($query) => $query->notArchived())
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(candidate.id)')
                    ->from('gps_locations as candidate')
                    ->whereNotNull('candidate.recorded_at')
                    ->whereRaw('candidate.recorded_at = (SELECT MAX(latest.recorded_at) FROM gps_locations AS latest WHERE latest.driver_id = candidate.driver_id)')
                    ->groupBy('candidate.driver_id');
            })
            ->get()
            ->keyBy('driver_id');

        $ambulances = Ambulance::notArchived()->with([
            'driverAssignments' => fn($query) => $query
                ->where('status', 'active')
                ->whereHas('driver', fn($driverQuery) => $driverQuery->notArchived())
                ->with('driver.user')
                ->latest('assigned_at'),
        ])->get()->map(function ($ambulance) use ($latestDriverLocations, $gpsFreshness) {
            $driver = $ambulance->driverAssignments->first()?->driver;
            $location = $driver ? $latestDriverLocations->get($driver->id) : null;
            $mapStatus = $this->resolveMapStatus(
                strtolower((string) ($ambulance->status ?? 'available')),
                $driver?->status
            );
            $gpsMetadata = $gpsFreshness->metadata($location);
            $hasCurrentGps = in_array($gpsMetadata['gps_status'], ['fresh', 'delayed'], true);

            return [
                'id' => $ambulance->id,
                'name' => $ambulance->vehicle_name ?? 'Ambulance',
                'plate_number' => $ambulance->plate_number,
                'latitude' => $hasCurrentGps && $location?->latitude !== null ? (float) $location->latitude : null,
                'longitude' => $hasCurrentGps && $location?->longitude !== null ? (float) $location->longitude : null,
                'status' => $ambulance->status ?? 'available',
                'status_key' => $mapStatus,
                'driver_name' => $driver?->user?->name ?? 'Unassigned',
                'speed_kmh' => $location?->speed_kmh,
                'speed_status' => $location?->speed_status,
                'speed_limit_kmh' => $location?->speed_limit_kmh,
                'type' => 'ambulance',
                ...$gpsMetadata,
            ];
        });

        $incidents = Incident::query()->notArchived()->open()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest()
            ->limit(100)
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
                    'address' => $incident->formattedAddress(),
                    'driver_name' => $incident->driver?->user?->name ?? 'Unassigned',
                    'last_updated' => $incident->updated_at?->format('M d, Y H:i') ?? 'Unknown',
                    'type_label' => 'incident',
                ];
            });

        $drivers = $latestDriverLocations->map(function (GpsLocation $location) use ($gpsFreshness) {
            $driver = $location?->driver;
            $assignment = $driver?->activeVehicleAssignment()->first();
            $ambulance = $assignment?->ambulance;

            if (!$driver || !$location) {
                return null;
            }

            $driverStatus = strtolower((string) ($driver->status ?? 'available'));
            $mapStatus = $this->resolveMapStatus($driverStatus, $driverStatus);
            $gpsMetadata = $gpsFreshness->metadata($location);
            $hasCurrentGps = in_array($gpsMetadata['gps_status'], ['fresh', 'delayed'], true);

            return [
                'id' => $driver->id,
                'driver_name' => $driver->user?->name ?? 'Driver',
                'ambulance_unit' => $ambulance?->vehicle_name ?? 'Unassigned',
                'latitude' => $hasCurrentGps ? (float) $location->latitude : null,
                'longitude' => $hasCurrentGps ? (float) $location->longitude : null,
                'status' => $driver->status ?? 'available',
                'status_key' => $mapStatus,
                'speed_kmh' => $location->speed_kmh,
                'speed_status' => $location->speed_status,
                'speed_limit_kmh' => $location->speed_limit_kmh,
                'type' => 'driver',
                ...$gpsMetadata,
            ];
        })->filter();

        $vulnerableAreas = VulnerableArea::query()
            ->where('status', 'active')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(fn(VulnerableArea $area) => [
                'id' => $area->id,
                'name' => $area->name,
                'address' => $area->address,
                'vulnerability_level' => $area->vulnerability_level,
                'area_type' => $area->area_type,
                'latitude' => (float) $area->latitude,
                'longitude' => (float) $area->longitude,
                'type_label' => 'vulnerable_area',
            ]);

        return response()->json([
            'ambulances' => $ambulances->values(),
            'incidents' => $incidents->values(),
            'drivers' => $drivers->values(),
            'vulnerable_areas' => $vulnerableAreas->values(),
            'generated_at' => now()->toISOString(),
        ]);
    }

    public function liveCommandMapData(GpsFreshnessService $gpsFreshness)
    {
        return $this->gpsLocations($gpsFreshness);
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
    public function fleetReadiness(GpsFreshnessService $gpsFreshness)
    {
        $latestEligibleLocations = GpsLocation::query()
            ->whereHas('driver', fn($query) => $query->dispatchEligible())
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(candidate.id)')
                    ->from('gps_locations as candidate')
                    ->whereNotNull('candidate.recorded_at')
                    ->whereRaw('candidate.recorded_at = (SELECT MAX(latest.recorded_at) FROM gps_locations AS latest WHERE latest.driver_id = candidate.driver_id)')
                    ->groupBy('candidate.driver_id');
            })
            ->get();
        $onlineDrivers = $latestEligibleLocations
            ->filter(fn(GpsLocation $location) => in_array(
                $gpsFreshness->metadata($location)['gps_status'],
                ['fresh', 'delayed'],
                true
            ))
            ->count();
        $activeAmbulances = Ambulance::notArchived()->whereIn('status', ['available', 'in_use'])->count();
        $underMaintenance = Ambulance::notArchived()->where('status', 'maintenance')->count();
        $totalAmbulances = Ambulance::notArchived()->count();
        $availableAmbulances = Ambulance::available()->count();

        return response()->json([
            'available_ambulances' => $availableAmbulances,
            'active_ambulances' => $activeAmbulances,
            'vehicles_maintenance' => $underMaintenance,
            'drivers_online' => $onlineDrivers,
            'fleet_utilization' => $totalAmbulances > 0 ? round(($activeAmbulances / $totalAmbulances) * 100, 2) : 0,
        ]);
    }
}
