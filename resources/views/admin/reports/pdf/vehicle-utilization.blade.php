<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Vehicle Utilization Report</title>
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
            background: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2>Vehicle Utilization Report</h2>
    <table>
        <thead>
            <tr>
                <th>Plate Number</th>
                <th>Vehicle</th>
                <th>Status</th>
                <th>Usage Count</th>
                <th>Total Dispatches</th>
                <th>Downtime</th>
                <th>Maintenance Count</th>
                <th>Availability Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vehicles as $vehicle)
            <tr>
                <td>{{ $vehicle->plate_number }}</td>
                <td>{{ $vehicle->vehicle_name }}</td>
                <td>{{ $vehicle->status }}</td>
                <td>{{ $vehicle->usage_count }}</td>
                <td>{{ $vehicle->total_dispatches }}</td>
                <td>{{ $vehicle->downtime }} day(s)</td>
                <td>{{ $vehicle->maintenance_count }}</td>
                <td>{{ $vehicle->availability_rate }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>