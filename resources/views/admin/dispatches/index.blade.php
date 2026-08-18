@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <div>
        <h2 class="section-heading mb-1">Dispatch Center</h2>
        <p class="section-excerpt mb-0">Assign drivers and ambulances to active incidents with clarity and speed.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-4">
    @forelse($incidents as $incident)
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 admin-card">
            <div class="card-header bg-danger text-white border-0 rounded-top-4">
                <div class="d-flex justify-content-between align-items-center">
                    <strong>{{ $incident->incident_number ?? 'Incident' }}</strong>
                    <span class="badge bg-danger text-white">{{ ucfirst($incident->status) }}</span>
                </div>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Location:</strong> {{ $incident->location }}</p>
                <p class="mb-3"><strong>Type:</strong> {{ $incident->incident_type }}</p>

                <form action="{{ route('admin.dispatches.assign', $incident) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Driver</label>
                        <select name="driver_id" class="form-select" required>
                            <option value="">Select Driver</option>
                            @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">
                                {{ $driver->user->name }}
                                ({{ $driver->badge_id }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ambulance</label>
                        <select name="ambulance_id" class="form-select" required>
                            <option value="">Select Ambulance</option>
                            @foreach($ambulances as $ambulance)
                            <option value="{{ $ambulance->id }}">
                                {{ $ambulance->vehicle_name }}
                                ({{ $ambulance->plate_number }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-danger w-100">
                        Dispatch Vehicle
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-light text-muted">No dispatch assignments pending.</div>
    </div>
    @endforelse
</div>
<script>
    // Automatically refresh the Dispatch Center every 5 seconds
    setInterval(function() {
        window.location.reload();
    }, 5000);
</script>
@endsection