@extends('layouts.admin')

@section('content')

<h1>Dispatch Incident</h1>

<p>
    Incident:
    {{ $incident->incident_number }}
</p>

@if($nearestDriver || $nearestAmbulance)

<div class="card border-success shadow-sm mb-4">

    <div class="card-header bg-success text-white">
        ⭐ Nearest Available Resource
    </div>

    <div class="card-body">

        @if($nearestDriver)

        <h5 class="text-success">
            🚑 Recommended Driver
        </h5>

        <p class="mb-1">
            <strong>
                {{ $nearestDriver->user->name ?? $nearestDriver->badge_id }}
            </strong>
        </p>

        <span class="badge bg-primary">
            {{ round($nearestDistance,2) }} KM Away
        </span>

        @php
        $eta = max(1, ceil(($nearestDistance / 40) * 60));
        @endphp

        <span class="badge bg-warning text-dark">
            ETA {{ $eta }} mins
        </span>

        @endif

        <hr>

        @if($nearestAmbulance)

        <h5 class="text-danger">
            🚐 Recommended Ambulance
        </h5>

        <p class="mb-1">

            <strong>
                {{ $nearestAmbulance->plate_number }}
            </strong>

            <br>

            {{ $nearestAmbulance->vehicle_name }}

        </p>

        <span class="badge bg-success">

            {{ round($nearestAmbulanceDistance,2) }} KM Away

        </span>

        @php
        $etaVehicle = max(1, ceil(($nearestAmbulanceDistance / 40) * 60));
        @endphp

        <span class="badge bg-warning text-dark">

            ETA {{ $etaVehicle }} mins

        </span>

        <span class="badge bg-danger">

            ⭐ Recommended

        </span>

        @endif

    </div>

</div>

@endif

<form method="POST"
    action="{{ route('admin.incidents.dispatch', $incident) }}">

    @csrf

    <div class="mb-3">

        <label>Driver</label>

        <select name="driver_id"
            class="form-control"
            @if($drivers->isEmpty()) disabled @endif>

            @if($drivers->isEmpty())
            <option value="">No available drivers</option>
            @else
            @foreach($drivers as $driver)

            <option
                value="{{ $driver->id }}"
                {{ isset($nearestDriver) && $nearestDriver?->id == $driver->id ? 'selected' : '' }}>
                @if(isset($nearestDriver) && $nearestDriver?->id == $driver->id)

                ⭐ {{ $driver->badge_id }}

                @else

                {{ $driver->badge_id }}

                @endif
            </option>

            @endforeach
            @endif

        </select>

    </div>

    <div class="alert alert-info mb-3">
        The assigned driver will select the vehicle when accepting the dispatch. Admin vehicle records remain available for management and monitoring.
    </div>

    <button class="btn btn-primary" @if($drivers->isEmpty()) disabled @endif>
        Dispatch Incident
    </button>

</form>

@endsection