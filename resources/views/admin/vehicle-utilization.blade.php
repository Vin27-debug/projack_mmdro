@extends('layouts.admin')

@section('content')

<div class="container">

    <h2 class="mb-4">
        Vehicle Utilization Report
    </h2>

    <table class="table table-bordered">

        <thead>
            <tr>
                <th>Plate Number</th>
                <th>Vehicle</th>
                <th>Status</th>
                <th>Vehicle Usage Count</th>
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
                <td>{{ strtoupper($vehicle->status) }}</td>
                <td>{{ $vehicle->usage_count }}</td>
                <td>{{ $vehicle->total_dispatches }}</td>
                <td>{{ $vehicle->downtime }} day(s)</td>
                <td>{{ $vehicle->maintenance_count }}</td>
                <td>{{ $vehicle->availability_rate }}%</td>
            </tr>
            @endforeach

        </tbody>

    </table>

</div>

@endsection