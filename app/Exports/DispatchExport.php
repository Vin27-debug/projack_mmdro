<?php

namespace App\Exports;

use App\Models\Dispatch;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DispatchExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Dispatch::select(
            'incident_id',
            'driver_id',
            'vehicle_id',
            'status',
            'assigned_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Incident ID',
            'Driver ID',
            'Vehicle ID',
            'Status',
            'Assigned At'
        ];
    }
}