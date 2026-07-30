@extends('layouts.admin')

@section('content')

<div class="container-fluid">

    <h2 class="mb-4">
        Vehicle Hijack Alerts
    </h2>

    <table class="table table-bordered table-striped">

        <thead class="table-danger">

            <tr>
                <th>ID</th>
                <th>Driver</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Status</th>
                <th>Triggered At</th>
            </tr>

        </thead>

        <tbody>

        @foreach($alerts as $alert)

            <tr>

                <td>{{ $alert->id }}</td>

                <td>
                    {{ $alert->driver?->user?->name ?? 'N/A' }}
                </td>

                <td>{{ $alert->latitude }}</td>

                <td>{{ $alert->longitude }}</td>

                <td>

                    <span class="badge bg-danger">
                        {{ strtoupper($alert->status) }}
                    </span>

                </td>

                <td>{{ $alert->triggered_at }}</td>

            </tr>

        @endforeach

        </tbody>

    </table>

</div>

@endsection