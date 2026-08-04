@extends('layouts.superadmin')

@section('content')
<div class="page-header">
    <div>
        <div class="small text-uppercase text-white-50 mb-2">Assignments</div>
        <h1 class="page-title">Driver Assignments</h1>
        <p class="page-subtitle mb-0">Review and create active driver-to-ambulance assignments.</p>
    </div>
    <a href="{{ route('assignments.create') }}" class="btn btn-primary page-back-button">
        <i class="bi bi-plus-lg me-1"></i> New Assignment
    </a>
</div>

<div class="card admin-card border-0 shadow-sm p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Driver</th>
                    <th>Ambulance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $assignment)
                <tr>
                    <td>#{{ $assignment->id }}</td>
                    <td>{{ $assignment->driver->badge_id }} — {{ $assignment->driver->user->name }}</td>
                    <td>{{ $assignment->ambulance->plate_number }} — {{ $assignment->ambulance->vehicle_name }}</td>
                    <td>{{ ucfirst($assignment->status) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No assignments created yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection