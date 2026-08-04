@extends('layouts.admin')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="section-heading mb-1">Vehicle Maintenance</h2>
            <p class="section-excerpt mb-0">Track vehicle upkeep, maintenance history, and availability.</p>
        </div>
        <a href="{{ route('admin.maintenance.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Maintenance Record
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-4 border-primary shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="text-uppercase small text-muted">Total Vehicles</div>
                    <div class="display-6 fw-bold text-white mt-2">
                        {{ $stats['total_vehicles'] }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-4 border-success shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="text-uppercase small text-muted">Active Vehicles</div>
                    <div class="display-6 fw-bold text-white mt-2">
                        {{ $stats['active_vehicles'] }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-4 border-warning shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="text-uppercase small text-muted">Vehicles Under Maintenance</div>
                    <div class="display-6 fw-bold text-white mt-2">
                        {{ $stats['maintenance_vehicles'] }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-4 border-info shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="text-uppercase small text-muted">Available Vehicles</div>
                    <div class="display-6 fw-bold text-white mt-2">
                        {{ $stats['available_vehicles'] }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Vehicle</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Cost</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenances as $maintenance)
                        <tr>
                            <td>{{ $maintenance->ambulance?->vehicle_name ?? 'Unknown vehicle' }}</td>
                            <td>{{ $maintenance->scheduled_date?->format('M d, Y') }}</td>
                            <td>{{ $maintenance->maintenance_type }}</td>
                            <td>${{ number_format(0, 2) }}</td>
                            <td>{{ $maintenance->description ?: '—' }}</td>
                            <td>
                                @if($maintenance->status === 'completed')
                                <span class="badge bg-success">Completed</span>
                                @elseif($maintenance->status === 'cancelled')
                                <span class="badge bg-danger">Cancelled</span>
                                @elseif($maintenance->status === 'in_progress')
                                <span class="badge bg-warning text-dark">In Progress</span>
                                @else
                                <span class="badge bg-info text-dark">Scheduled</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.maintenance.edit', $maintenance) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    @if($maintenance->status !== 'completed')
                                    <form method="POST" action="{{ route('admin.maintenance.complete', $maintenance) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-success btn-sm">Complete</button>
                                    </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.maintenance.destroy', $maintenance) }}" class="d-inline" onsubmit="return confirm('Delete this maintenance record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No maintenance records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $maintenances->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection