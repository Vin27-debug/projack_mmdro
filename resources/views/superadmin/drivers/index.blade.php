@extends('layouts.superadmin')

@section('content')
<div class="page-header">
    <div>
        <div class="small text-uppercase text-white-50 mb-2">Driver Management</div>
        <h1 class="page-title">Approved Drivers</h1>
        <p class="page-subtitle mb-0">All drivers currently approved for dispatch and monitoring.</p>
    </div>
    <a href="{{ route('superadmin.dashboard') }}" class="btn btn-outline-light page-back-button">
        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
    </a>
</div>

<div class="card admin-card border-0 shadow-sm p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Badge</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>License</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $driver)
                <tr>
                    <td>#{{ $driver->id }}</td>
                    <td>{{ $driver->badge_id }}</td>
                    <td>{{ $driver->user->name }}</td>
                    <td>{{ $driver->user->email }}</td>
                    <td>{{ $driver->license_number }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No approved drivers found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection