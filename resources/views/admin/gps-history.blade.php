@extends('layouts.admin')

@section('content')

<h2>GPS History</h2>

<table class="table table-bordered">

    <thead>
        <tr>
            <th>Driver</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Speed</th>
            <th>Limit</th>
            <th>Recorded At</th>
        </tr>
    </thead>

    <tbody>

        @foreach($locations as $location)

        <tr>

            <td>
                {{ $location->speed_limit_kmh !== null ? number_format($location->speed_limit_kmh, 1) . ' km/h' : 'UNRATED' }}
            </td>

            <td>
                {{ $location->driver?->user?->name }}
            </td>

            <td>
                {{ $location->latitude }}
            </td>

            <td>
                {{ $location->longitude }}
            </td>

            <td>
                @if($location->speed_kmh !== null)
                {{ number_format($location->speed_kmh, 1) }} km/h
                <span class="badge bg-{{ $location->speed_status === 'red' ? 'danger' : ($location->speed_status === 'yellow' ? 'warning text-dark' : ($location->speed_status === 'green' ? 'success' : 'secondary')) }}">
                    {{ $location->speed_status ?: 'unrated' }}
                </span>
                @else
                Unavailable
                @endif
            </td>

            <td>
                {{ $location->recorded_at?->format('M d, Y h:i A') ?: $location->created_at?->format('M d, Y h:i A') }}
            </td>

        </tr>

        @endforeach

    </tbody>

</table>

{{ $locations->links() }}

@endsection