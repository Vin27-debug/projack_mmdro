<?php

namespace App\Exports;

use App\Models\Incident;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class IncidentExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Incident::select(
            'incident_number',
            'reporter_name',
            'incident_type',
            'location',
            'status',
            'created_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'Incident Number',
            'Reporter',
            'Incident Type',
            'Location',
            'Status',
            'Created At'
        ];
    }
}