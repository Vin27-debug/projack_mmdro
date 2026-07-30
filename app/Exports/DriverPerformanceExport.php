<?php

namespace App\Exports;

use App\Models\Driver;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DriverPerformanceExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Driver::withCount([
            'dispatches',
            'reports'
        ])
            ->get()
            ->map(function ($driver) {
                return [
                    $driver->badge_id,
                    $driver->user?->name,
                    $driver->dispatches_count,
                    $driver->reports_count,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Badge ID',
            'Driver Name',
            'Total Dispatches',
            'Total Reports'
        ];
    }
}
