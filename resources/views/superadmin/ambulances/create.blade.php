@extends('layouts.superadmin')

@section('content')
<div class="page-header">
    <div>
        <div class="small text-uppercase text-white-50 mb-2">Fleet Management</div>
        <h1 class="page-title">Add Ambulance</h1>
        <p class="page-subtitle mb-0">Register a new vehicle for the super admin fleet.</p>
    </div>
    <a href="{{ route('ambulances.index') }}" class="btn btn-outline-light page-back-button">
        <i class="bi bi-arrow-left me-1"></i> Back to Fleet
    </a>
</div>

<div class="card admin-card border-0 shadow-sm p-4">
    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('ambulances.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Plate Number</label>
            <input type="text" name="plate_number" class="form-control" value="{{ old('plate_number') }}" placeholder="ABC-1234">
        </div>

        <div class="mb-3">
            <label class="form-label">Vehicle Name</label>
            <input type="text" name="vehicle_name" class="form-control" value="{{ old('vehicle_name') }}" placeholder="Toyota HiAce">
        </div>

        <div class="mb-3">
            <label class="form-label">Vehicle Type</label>
            <select name="vehicle_type" class="form-select">
                <option value="ambulance" {{ old('vehicle_type') == 'ambulance' ? 'selected' : '' }}>Ambulance</option>
                <option value="rescue_van" {{ old('vehicle_type') == 'rescue_van' ? 'selected' : '' }}>Rescue Van</option>
                <option value="fire_truck" {{ old('vehicle_type') == 'fire_truck' ? 'selected' : '' }}>Fire Truck</option>
            </select>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('ambulances.index') }}" class="btn btn-outline-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Ambulance</button>
        </div>
    </form>
</div>
@endsection