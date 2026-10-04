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
        ⭐ Recommended Dispatch Pairing
    </div>
    <div class="card-body">
        @if($nearestDriver)
        <h5 class="text-success mb-2">🚑 Recommended Driver</h5>
        <p class="mb-1"><strong>{{ $nearestDriver->user->name ?? $nearestDriver->badge_id }}</strong></p>
        <p class="mb-2 text-muted">{{ $nearestDriver->badge_id }} · {{ round($nearestDistance, 2) }} km away</p>
        <span class="badge bg-primary">{{ round($nearestDistance, 2) }} KM Away</span>
        @php $eta = max(1, ceil(($nearestDistance / 40) * 60)); @endphp
        <span class="badge bg-warning text-dark">ETA {{ $eta }} mins</span>
        @endif

        @if($nearestDriver && $nearestAmbulance)
        <hr>
        @endif

        @if($nearestAmbulance)
        <h5 class="text-danger mb-2">🚐 Recommended Ambulance</h5>
        <p class="mb-1"><strong>{{ $nearestAmbulance->plate_number }}</strong> · {{ $nearestAmbulance->vehicle_name }}</p>
        <p class="mb-2 text-muted">{{ round($nearestAmbulanceDistance, 2) }} km away</p>
        <span class="badge bg-success">{{ round($nearestAmbulanceDistance, 2) }} KM Away</span>
        @php $etaVehicle = max(1, ceil(($nearestAmbulanceDistance / 40) * 60)); @endphp
        <span class="badge bg-warning text-dark">ETA {{ $etaVehicle }} mins</span>
        <span class="badge bg-danger">⭐ Recommended</span>
        @endif
    </div>
</div>
@endif

<form method="POST" action="{{ route('admin.incidents.dispatch', $incident) }}">
    @csrf

    <div class="mb-3">
        <label class="form-label fw-semibold">Driver</label>
        <select name="driver_id" class="form-control" @if($drivers->isEmpty()) disabled @endif>
            @if($drivers->isEmpty())
            <option value="">No available drivers</option>
            @else
            @foreach($drivers as $driver)
            @php $isRecommended = isset($nearestDriver) && $nearestDriver?->id == $driver->id; @endphp
            <option value="{{ $driver->id }}" {{ $isRecommended ? 'selected' : '' }}>
                {{ $isRecommended ? '⭐ ' : '' }}{{ $driver->badge_id }}{{ $driver->user ? ' - ' . $driver->user->name : '' }}{{ isset($driver->distance) ? ' (' . $driver->distance . ' km)' : '' }}
            </option>
            @endforeach
            @endif
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Ambulance / Vehicle</label>
        <select name="vehicle_id" class="form-control" @if($vehicles->isEmpty()) disabled @endif>
            @if($vehicles->isEmpty())
            <option value="">No available vehicles</option>
            @else
            @foreach($vehicles as $vehicle)
            @php $isRecommendedVehicle = isset($nearestAmbulance) && $nearestAmbulance?->id == $vehicle->id; @endphp
            <option value="{{ $vehicle->id }}" {{ $isRecommendedVehicle ? 'selected' : '' }}>
                {{ $isRecommendedVehicle ? '⭐ ' : '' }}{{ $vehicle->plate_number }} · {{ $vehicle->vehicle_name }}{{ isset($vehicle->distance) ? ' (' . $vehicle->distance . ' km)' : '' }}
            </option>
            @endforeach
            @endif
        </select>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h5 class="mb-3">Nearest eligible resources</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Driver</th>
                            <th>GPS status</th>
                            <th>Distance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recommendation['rankedDrivers'] ?? [] as $rankedDriver)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $rankedDriver->badge_id }} {{ $rankedDriver->user ? ' - ' . $rankedDriver->user->name : '' }}</td>
                            <td>{{ isset($rankedDriver->gps_age_minutes) ? 'Fresh (' . $rankedDriver->gps_age_minutes . ' min ago)' : 'Fresh' }}</td>
                            <td>{{ $rankedDriver->distance ?? '—' }} km</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No eligible drivers are currently within a fresh GPS window.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vehicle</th>
                            <th>Vehicle status</th>
                            <th>Distance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recommendation['rankedVehicles'] ?? [] as $rankedVehicle)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $rankedVehicle->plate_number }} · {{ $rankedVehicle->vehicle_name }}</td>
                            <td>{{ ucfirst($rankedVehicle->status) }}</td>
                            <td>{{ $rankedVehicle->distance ?? '—' }} km</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No eligible vehicles are available for dispatch.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <button class="btn btn-primary" @if($drivers->isEmpty() || $vehicles->isEmpty()) disabled @endif>
        Dispatch Incident
    </button>
</form>

@endsection