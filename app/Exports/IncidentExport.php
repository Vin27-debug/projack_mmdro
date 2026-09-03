<?php

namespace App\Exports;

use App\Models\Incident;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class IncidentExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Incident::with('dispatches')->get()->map(function (Incident $incident): array {
            $dispatch = $incident->dispatches->sortByDesc('created_at')->first();
            return [
                $incident->incident_number,
                $incident->reporter_name,
                $incident->incident_type,
                $incident->location,
                $incident->house_number,
                $incident->street,
                $incident->barangay,
                $incident->city,
                $incident->province,
                $incident->status,
                $incident->call_received_at,
                $incident->response_at,
                $incident->at_scene_at,
                $incident->at_patient_at,
                $incident->depart_scene_at,
                $incident->at_hospital_at,
                $incident->created_at,
                $dispatch?->created_at,
                $dispatch?->accepted_at,
                $dispatch?->declined_at,
                $dispatch?->en_route_at,
                $dispatch?->arrived_at,
                $incident->completed_at,
                $incident->closed_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Incident Number',
            'Reporter',
            'Incident Type',
            'Location',
            'House Number',
            'Street',
            'Barangay',
            'City / Municipality',
            'Province',
            'Status',
            'Call Received At',
            'Response At',
            'At Scene At',
            'At Patient At',
            'Depart Scene At',
            'At Hospital At',
            'Created At',
            'Dispatch Created At',
            'Accepted At',
            'Declined At',
            'En Route At',
            'Arrived At',
            'Completed At',
            'Closed At'
        ];
    }
}
