<?php

namespace App\Services;

use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Models\Incident;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportsService
{
    public function getSummary(array $filters = []): array
    {
        $query = Incident::query();

        $this->applyDateRange($query, $filters);

        $totalIncidents = (clone $query)->count();
        $completedIncidents = (clone $query)->where('status', 'completed')->count();
        $pendingIncidents = (clone $query)->where('status', 'pending')->count();
        $activeIncidents = (clone $query)->whereNotIn('status', ['completed', 'closed'])->count();

        return [
            'total_incidents' => $totalIncidents,
            'completed_incidents' => $completedIncidents,
            'pending_incidents' => $pendingIncidents,
            'active_incidents' => $activeIncidents,
        ];
    }

    public function getDriverPerformance(array $filters = []): Collection
    {
        $dispatchesQuery = Dispatch::query()->with(['incident', 'driver.user', 'vehicle']);
        $this->applyDateRange($dispatchesQuery, $filters);

        $dispatches = $dispatchesQuery->get();

        return $dispatches->groupBy(fn(Dispatch $dispatch) => $dispatch->driver_id)
            ->map(function (Collection $group) {
                $driver = $group->first()->driver;
                $responseTimes = $group->map(function (Dispatch $dispatch): ?int {
                    if (!$dispatch->assigned_at || !$dispatch->arrived_at) {
                        return null;
                    }

                    return (int) $dispatch->assigned_at->diffInMinutes($dispatch->arrived_at);
                })->filter()->values();

                return (object) [
                    'driver' => $driver,
                    'dispatch_count' => $group->count(),
                    'completed_dispatches' => $group->where('status', Dispatch::STATUS_COMPLETED)->count(),
                    'average_response_time' => $responseTimes->isNotEmpty() ? round($responseTimes->avg(), 2) : 0,
                    'fastest_response' => $responseTimes->isNotEmpty() ? (int) $responseTimes->min() : 0,
                    'slowest_response' => $responseTimes->isNotEmpty() ? (int) $responseTimes->max() : 0,
                ];
            })->values();
    }

    public function getVehicleUtilization(array $filters = []): Collection
    {
        $ambulances = Ambulance::query()
            ->with(['dispatches' => function ($query) use ($filters) {
                $this->applyDateRange($query, $filters);
            }, 'maintenances'])
            ->get();

        return $ambulances->map(function (Ambulance $ambulance): object {
            $dispatches = $ambulance->dispatches ?? collect();
            $maintenances = $ambulance->maintenances ?? collect();
            $totalDispatches = $dispatches->count();
            $completedDispatches = $dispatches->where('status', Dispatch::STATUS_COMPLETED)->count();
            $maintenanceCount = $maintenances->count();
            $availabilityRate = $totalDispatches > 0 ? round(($completedDispatches / $totalDispatches) * 100, 2) : 100;

            return (object) [
                'ambulance' => $ambulance,
                'usage_count' => $totalDispatches,
                'total_dispatches' => $totalDispatches,
                'completed_dispatches' => $completedDispatches,
                'maintenance_count' => $maintenanceCount,
                'availability_rate' => $availabilityRate,
                'downtime' => 0,
            ];
        });
    }

    public function getResponseTimeMetrics(array $filters = []): array
    {
        $dispatchesQuery = Dispatch::query()->with(['incident', 'driver.user', 'vehicle']);
        $this->applyDateRange($dispatchesQuery, $filters);

        $dispatches = $dispatchesQuery->whereNotNull('assigned_at')->whereNotNull('arrived_at')->get();

        $responseTimes = $dispatches->map(function (Dispatch $dispatch): ?int {
            if (!$dispatch->assigned_at || !$dispatch->arrived_at) {
                return null;
            }

            return (int) $dispatch->assigned_at->diffInMinutes($dispatch->arrived_at);
        })->filter()->values();

        return [
            'dispatches' => $dispatches,
            'average_response_time' => $responseTimes->isNotEmpty() ? round($responseTimes->avg(), 2) : 0,
            'fastest_response' => $responseTimes->isNotEmpty() ? (int) $responseTimes->min() : 0,
            'slowest_response' => $responseTimes->isNotEmpty() ? (int) $responseTimes->max() : 0,
            'completed_responses' => $dispatches->count(),
        ];
    }

    public function getMonthlyIncidentTrends(array $filters = []): array
    {
        $query = Incident::query();
        $this->applyDateRange($query, $filters);

        $driverName = DB::connection()->getDriverName();
        $monthExpression = $driverName === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $monthly = (clone $query)
            ->selectRaw($monthExpression . ' as month')
            ->selectRaw('COUNT(*) as total')
            ->whereNotNull('created_at')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');

        return [
            'labels' => $monthly->keys()->all(),
            'series' => $monthly->values()->all(),
        ];
    }

    protected function applyDateRange($query, array $filters = []): void
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
    }
}
