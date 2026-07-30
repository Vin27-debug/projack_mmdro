<h2>Driver Performance Report</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>Driver</th>
        <th>Total Dispatches</th>
        <th>Total Reports</th>
    </tr>

    @foreach($drivers as $driver)
    <tr>
        <td>{{ $driver->user->name ?? 'N/A' }}</td>
        <td>{{ $driver->dispatches_count }}</td>
        <td>{{ $driver->incident_reports_count }}</td>
    </tr>
    @endforeach
</table>