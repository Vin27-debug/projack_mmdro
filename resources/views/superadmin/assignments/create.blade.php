@extends('layouts.superadmin')

@section('content')
<div class="page-header">
    <div>
        <div class="small text-uppercase text-white-50 mb-2">Assignments</div>
        <h1 class="page-title">Assign Driver to Ambulance</h1>
        <p class="page-subtitle mb-0">Create a new operative assignment between a driver and a vehicle.</p>
    </div>
    <a href="{{ route('assignments.index') }}" class="btn btn-outline-light page-back-button">
        <i class="bi bi-arrow-left me-1"></i> Back to Assignments
    </a>
</div>

<div class="card admin-card border-0 shadow-sm p-4">
    <form method="POST" action="{{ route('assignments.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Driver</label>
            <select name="driver_id" class="form-select">
                @foreach($drivers as $driver)
                <option value="{{ $driver->id }}">{{ $driver->badge_id }} — {{ $driver->user->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Ambulance</label>
            <select name="ambulance_id" class="form-select">
                @foreach($ambulances as $ambulance)
                <option value="{{ $ambulance->id }}">{{ $ambulance->plate_number }} — {{ $ambulance->vehicle_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('assignments.index') }}" class="btn btn-outline-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Assign</button>
        </div>
    </form>
</div>
@endsection