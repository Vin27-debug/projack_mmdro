<?php

namespace App\Exports;

use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Models\VehicleMaintenance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VehicleUtilizationExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Ambulance::with(['dispatches', 'maintenances'])
            ->get()
            ->map(function (Ambulance $vehicle) {
                $totalDispatches = $vehicle->dispatches->count();
                $completedDispatches = $vehicle->dispatches
                    ->where('status', Dispatch::STATUS_COMPLETED)
                    ->count();
                $maintenanceCount = $vehicle->maintenances->count();
                $downtime = $vehicle->maintenances
                    ->filter(fn(VehicleMaintenance $maintenance) => $maintenance->completed_date && $maintenance->scheduled_date)
                    ->sum(fn(VehicleMaintenance $maintenance) => $maintenance->scheduled_date->diffInDays($maintenance->completed_date));
                $availabilityRate = $totalDispatches > 0
                    ? round(($completedDispatches / $totalDispatches) * 100, 2)
                    : 100;

                return [
                    $vehicle->plate_number,
                    $vehicle->vehicle_name,
                    $vehicle->status,
                    $totalDispatches,
                    $totalDispatches,
                    $downtime,
                    $maintenanceCount,
                    $availabilityRate,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Plate Number',
            'Vehicle Name',
            'Status',
            'Vehicle Usage Count',
            'Total Dispatches',
            'Downtime',
            'Maintenance Count',
            'Availability Rate (%)',
        ];
    }
}
