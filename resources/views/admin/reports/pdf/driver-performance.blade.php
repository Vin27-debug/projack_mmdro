<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Driver Performance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #0d6efd;
            color: white;
        }
    </style>
</head>

<body>
    <h2>Driver Performance Report</h2>
    <table>
        <thead>
            <tr>
                <th>Driver</th>
                <th>Badge ID</th>
                <th>Completed Dispatches</th>
                <th>Average Response Time</th>
                <th>Average Arrival Time</th>
                <th>Completion Rate</th>
                <th>Incident Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($drivers as $driver)
            <tr>
                <td>{{ $driver->user?->name ?? 'Unknown Driver' }}</td>
                <td>{{ $driver->badge_id }}</td>
                <td>{{ $driver->completed_dispatches }}</td>
                <td>{{ $driver->average_response_time }} min</td>
                <td>{{ $driver->average_arrival_time }} min</td>
                <td>{{ $driver->completion_rate }}%</td>
                <td>{{ $driver->incident_count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>