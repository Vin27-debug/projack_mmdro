@extends('layouts.driver')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1">Driver Settings</h2>
            <p class="text-muted mb-0">Manage your profile and driver preferences.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="row gy-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="{{ auth()->user()->email }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Driver ID</label>
                    <input type="text" class="form-control" value="{{ $driver->id }}" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Vehicle</label>
                    <input type="text" class="form-control" value="{{ $driver->vehicle?->vehicle_name ?? 'Not assigned' }}" readonly>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Status</label>
                    <input type="text" class="form-control" value="{{ ucfirst($driver->status ?? 'available') }}" readonly>
                </div>
            </div>

            <div class="mt-4 text-end">
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">Edit Profile</a>
            </div>
        </div>
    </div>
</div>
@endsection