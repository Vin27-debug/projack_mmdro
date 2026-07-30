@extends('layouts.admin')

@section('content')

<div class="container">

    <h2 class="mb-4">
        GPS Location History
    </h2>

    <div class="card">

        <div class="card-body">

            <table class="table table-bordered">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Driver</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Recorded</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach($locations as $location)

                    <tr>

                        <td>{{ $location->id }}</td>

                        <td>
                            {{ $location->driver?->user?->name }}
                        </td>

                        <td>{{ $location->latitude }}</td>

                        <td>{{ $location->longitude }}</td>

                        <td>{{ $location->recorded_at }}</td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

            {{ $locations->links() }}

        </div>

    </div>

</div>

@endsection