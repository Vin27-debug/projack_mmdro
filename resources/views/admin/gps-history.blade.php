@extends('layouts.admin')

@section('content')

<h2>GPS History</h2>

<table class="table table-bordered">

    <thead>
        <tr>
            <th>Driver</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Recorded At</th>
        </tr>
    </thead>

    <tbody>

        @foreach($locations as $location)

        <tr>

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
                {{ $location->created_at }}
            </td>

        </tr>

        @endforeach

    </tbody>

</table>

{{ $locations->links() }}

@endsection