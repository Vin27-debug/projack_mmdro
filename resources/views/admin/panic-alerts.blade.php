@extends('layouts.admin')

@section('content')

<div class="container">

    <h2 class="mb-4">
        Active Panic Alerts
    </h2>

    <table class="table table-bordered">

        <thead class="table-danger">

            <tr>
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

                <td>
                    {{ $alert->driver?->user?->name }}
                </td>

                <td>
                    {{ $alert->latitude }}
                </td>

                <td>
                    {{ $alert->longitude }}
                </td>

                <td>
                    {{ $alert->status }}
                </td>

                <td>
                    {{ $alert->triggered_at }}
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>

@endsection