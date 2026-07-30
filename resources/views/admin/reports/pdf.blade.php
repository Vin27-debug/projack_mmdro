<!DOCTYPE html>
<html>

<head>
    <title>MuniResQ Incident Report</title>

    <style>

        body {
            font-family: DejaVu Sans;
        }

        table {
            width:100%;
            border-collapse:collapse;
        }

        th, td {
            border:1px solid #000;
            padding:8px;
            text-align:left;
        }

        th {
            background:#f0f0f0;
        }

    </style>

</head>

<body>

    <h1>MuniResQ Incident Report</h1>

    <p>
        Generated:
        {{ now()->format('F d, Y h:i A') }}
    </p>

    <table>

        <thead>

            <tr>
                <th>ID</th>
                <th>Incident</th>
                <th>Status</th>
                <th>Date</th>
            </tr>

        </thead>

        <tbody>

            @foreach($incidents as $incident)

            <tr>
                <td>{{ $incident->id }}</td>
                <td>{{ $incident->type ?? 'N/A' }}</td>
                <td>{{ $incident->status }}</td>
                <td>{{ $incident->created_at }}</td>
            </tr>

            @endforeach

        </tbody>

    </table>

</body>

</html>
