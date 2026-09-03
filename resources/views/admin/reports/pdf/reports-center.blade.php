<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>MuniResQ Emergency Reports Center</title>
    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 9px;
        }

        h1,
        h2 {
            text-align: center;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 4px;
        }

        h2 {
            font-size: 12px;
            margin-top: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }

        th {
            background: #f2f2f2;
        }

        .summary td {
            width: 25%;
        }

        .muted {
            color: #555;
        }
    </style>
</head>

<body>
    <h1>MuniResQ Emergency Reports Center</h1>
    <div class="muted">Generated: {{ now()->format('Y-m-d H:i:s') }}</div>

    <table class="summary">
        <tr>
            <th>Total Incidents</th>
            <th>Completed</th>
            <th>Pending</th>
            <th>Active</th>
        </tr>
        <tr>
            <td>{{ $summary['total_incidents'] ?? 0 }}</td>
            <td>{{ $summary['completed_incidents'] ?? 0 }}</td>
            <td>{{ $summary['pending_incidents'] ?? 0 }}</td>
            <td>{{ $summary['active_incidents'] ?? 0 }}</td>
        </tr>
    </table>

    <h2>Incident Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Incident #</th>
                <th>Reporter</th>
                <th>Type</th>
                <th>Location</th>
                <th>Status</th>
                <th>Call Received</th>
                <th>Response</th>
                <th>At Scene</th>
                <th>At Patient</th>
                <th>Depart Scene</th>
                <th>At Hospital</th>
                <th>Completed</th>
                <th>Closed</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incidents as $incident)
            <tr>
                <td>{{ $incident->incident_number }}</td>
                <td>{{ $incident->reporter_name }}</td>
                <td>{{ $incident->incident_type }}</td>
                <td>{{ $incident->location }}</td>
                <td>{{ ucfirst($incident->status) }}</td>
                <td>{{ $incident->call_received_at?->format('Y-m-d H:i:s') ?: 'N/A' }}</td>
                <td>{{ $incident->response_at?->format('Y-m-d H:i:s') ?: 'N/A' }}</td>
                <td>{{ $incident->at_scene_at?->format('Y-m-d H:i:s') ?: 'N/A' }}</td>
                <td>{{ $incident->at_patient_at?->format('Y-m-d H:i:s') ?: 'N/A' }}</td>
                <td>{{ $incident->depart_scene_at?->format('Y-m-d H:i:s') ?: 'N/A' }}</td>
                <td>{{ $incident->at_hospital_at?->format('Y-m-d H:i:s') ?: 'N/A' }}</td>
                <td>{{ $incident->completed_at?->format('Y-m-d H:i:s') ?: 'N/A' }}</td>
                <td>{{ $incident->closed_at?->format('Y-m-d H:i:s') ?: 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="13">No incidents found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>