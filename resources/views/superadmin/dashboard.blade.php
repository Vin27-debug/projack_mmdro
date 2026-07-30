@extends('layouts.superadmin')

@section('content')

@if(isset($activePanicAlerts) && $activePanicAlerts->count())

<div class="alert alert-danger shadow-sm mb-4">

    <h3>🚨 ACTIVE PANIC ALERTS</h3>

    @foreach($activePanicAlerts as $alert)

    <div class="border-bottom pb-2 mb-2">

        <strong>
            {{ $alert->driver->user->name ?? 'Unknown Driver' }}
        </strong>

        <br>

        Latitude:
        {{ $alert->latitude }}

        <br>

        Longitude:
        {{ $alert->longitude }}

        <br>

        Time:
        {{ $alert->triggered_at }}

    </div>

    @endforeach

</div>

@endif
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Superadmin Dashboard</h1>
        <p class="text-muted mb-0">Monitor incidents, drivers, and ambulance availability in one place.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted">Total Incidents</h5>
                <p class="display-6 fw-bold mb-0">{{ $stats['total_incidents'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted">Pending Incidents</h5>
                <p class="display-6 fw-bold mb-0">{{ $stats['pending_incidents'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted">Dispatched Incidents</h5>
                <p class="display-6 fw-bold mb-0">{{ $stats['dispatched_incidents'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted">Completed Incidents</h5>
                <p class="display-6 fw-bold mb-0">{{ $stats['completed_incidents'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted">Total Drivers</h5>
                <p class="display-6 fw-bold mb-0">{{ $stats['total_drivers'] }}</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted">Available Ambulances</h5>
                <p class="display-6 fw-bold mb-0">{{ $stats['available_ambulances'] }}</p>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0">Recent Incidents</h2>

    <a href="#" class="btn btn-sm btn-primary">
        View All
    </a>
</div>
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Incident Number</th>
                <th>Reporter</th>
                <th>Type</th>
                <th>Location</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentIncidents as $incident)
            <tr>
                <td>{{ $incident->incident_number }}</td>
                <td>{{ $incident->reporter_name }}</td>
                <td>{{ $incident->incident_type }}</td>
                <td>{{ $incident->location }}</td>
                <td><span class="badge bg-secondary text-uppercase">{{ $incident->status }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted">No incidents recorded yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
</div>
@endsection