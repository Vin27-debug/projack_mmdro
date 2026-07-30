<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Models\VehicleMaintenance;

class VehicleUtilizationController extends Controller
{
    public function index()
    {
        $vehicles = Ambulance::with(['dispatches', 'maintenances'])->get()->map(function (Ambulance $vehicle): object {
            $dispatches = $vehicle->dispatches ?? collect();
            $maintenances = $vehicle->maintenances ?? collect();

            $totalDispatches = $dispatches->count();
            $completedDispatches = $dispatches->where('status', Dispatch::STATUS_COMPLETED)->count();
            $maintenanceCount = $maintenances->count();

            $downtime = $maintenances
                ->filter(fn(VehicleMaintenance $maintenance) => $maintenance->completed_date && $maintenance->scheduled_date)
                ->sum(fn(VehicleMaintenance $maintenance) => $maintenance->scheduled_date->diffInDays($maintenance->completed_date));

            $availabilityRate = $totalDispatches > 0
                ? round(($completedDispatches / $totalDispatches) * 100, 2)
                : 100;

            $vehicle->usage_count = $totalDispatches;
            $vehicle->total_dispatches = $totalDispatches;
            $vehicle->downtime = $downtime;
            $vehicle->maintenance_count = $maintenanceCount;
            $vehicle->availability_rate = $availabilityRate;

            return $vehicle;
        });

        return view(
            'admin.vehicle-utilization',
            compact('vehicles')
        );
    }
}
