@extends('layouts.admin')

@section('content')

<div class="container-fluid">

    ```
    <h2 class="mb-4">
        🚑 Nearest Vehicle Recommendation
    </h2>

    <div class="card shadow mb-4">

        <div class="card-header bg-danger text-white">
            Active Incident
        </div>

        <div class="card-body">

            <p>
                <strong>Incident #:</strong>
                {{ $incident->incident_number }}
            </p>

            <p>
                <strong>Location:</strong>
                {{ $incident->location }}
            </p>

            <p>
                <strong>Status:</strong>
                {{ $incident->status }}
            </p>

        </div>

    </div>

    @if($recommendedVehicle)

    <div class="card border-success shadow mb-4">

        <div class="card-header bg-success text-white">

            ⭐ Recommended Vehicle

        </div>

        <form
            method="POST"
            action="{{ route('admin.auto-dispatch', $incident) }}">

            @csrf

            <button
                class="btn btn-success"
                type="submit">
                🚑 Dispatch Now
            </button>

        </form>

        <div class="card-body">

            <h4>
                {{ $recommendedVehicle->vehicle_name }}
            </h4>

            <p>
                Distance:
                <strong>
                    {{ $recommendedVehicle->distance }} KM
                </strong>
            </p>

            <p>
                Status:
                {{ $recommendedVehicle->status }}
            </p>

        </div>

    </div>

    @endif

    <div class="card shadow">

        <div class="card-header">
            Vehicle Distance Ranking
        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <thead>

                    <tr>
                        <th>#</th>
                        <th>Vehicle</th>
                        <th>Status</th>
                        <th>Distance (KM)</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach($vehicles as $vehicle)

                    <tr>

                        <td>
                            {{ $loop->iteration }}
                        </td>

                        <td>
                            {{ $vehicle->vehicle_name }}
                        </td>

                        <td>
                            {{ ucfirst($vehicle->status) }}
                        </td>

                        <td>
                            {{ $vehicle->distance }}
                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>
    ```

</div>

@endsection