@extends('layouts.superadmin')

@section('content')

<div class="page-header d-flex justify-content-between align-items-center">

    <div>
        <div class="small text-uppercase text-white-50 mb-2">
            Driver Management
        </div>

        <h1 class="page-title">
            Driver Management
        </h1>

        <p class="page-subtitle mb-0">
            Manage driver access and preserve dispatch history.
        </p>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('superadmin.drivers', ['archived' => $archived ? 0 : 1]) }}" class="btn btn-outline-light">
            {{ $archived ? 'Active Drivers' : 'Archived Drivers' }}
        </a>
        @unless($archived)
        <a
            href="{{ route('superadmin.drivers.create') }}"
            class="btn btn-primary">
            + Create Driver
        </a>
        @endunless
    </div>

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
                    <th>Driver Status</th>
                    <th>Assigned Vehicle</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($drivers as $driver)
                <tr>
                    {{-- ID --}}
                    <td>
                        #{{ $driver->id }}
                    </td>

                    {{-- BADGE --}}
                    <td>
                        {{ $driver->badge_id ?? 'Not assigned' }}
                    </td>

                    {{-- NAME --}}
                    <td>
                        {{ $driver->user?->name ?? 'Unknown' }}
                    </td>

                    {{-- EMAIL --}}
                    <td>
                        {{ $driver->user?->email ?? 'N/A' }}
                    </td>

                    <td>
                        @if($driver->archived_at)
                        <span class="badge bg-dark">Archived</span>
                        @elseif($driver->management_status === 'active')
                        <span class="badge bg-success">Active</span>
                        @elseif($driver->management_status === 'suspended')
                        <span class="badge bg-secondary">Suspended</span>
                        @else
                        <span class="badge bg-secondary">Unavailable</span>
                        @endif
                    </td>

                    {{-- ASSIGNED VEHICLE --}}
                    <td>
                        @if($driver->activeVehicleAssignment?->ambulance)
                        <div class="fw-semibold">
                            {{ $driver->activeVehicleAssignment->ambulance->vehicle_name }}
                        </div>

                        <div class="small text-muted">
                            {{ $driver->activeVehicleAssignment->ambulance->plate_number }}
                        </div>

                        <div class="small text-muted">
                            {{ ucwords(str_replace('_', ' ', $driver->activeVehicleAssignment->ambulance->vehicle_type)) }}
                        </div>
                        @else
                        <span class="badge bg-warning text-dark">
                            No Vehicle
                        </span>
                        @endif
                    </td>

                    {{-- ACTION --}}
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @if($archived)
                            <form method="POST" action="{{ route('superadmin.drivers.restore', $driver) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-success">Restore</button>
                            </form>
                            @else
                                @if($driver->user)
                                <form method="POST" action="{{ route('superadmin.drivers.status', $driver) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="active">
                                    <button class="btn btn-sm {{ $driver->management_status === 'active' ? 'btn-success' : 'btn-outline-success' }}" {{ $driver->management_status === 'active' ? 'disabled' : '' }}>Active</button>
                                </form>
                                <form method="POST" action="{{ route('superadmin.drivers.status', $driver) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="suspended">
                                    <button class="btn btn-sm {{ $driver->management_status === 'suspended' ? 'btn-secondary' : 'btn-outline-secondary' }}" {{ $driver->management_status === 'suspended' ? 'disabled' : '' }}>Suspended</button>
                                </form>
                                @endif
                                @if($driver->user && $driver->management_status === 'active')
                                <a href="{{ route('superadmin.drivers.assign', $driver) }}" class="btn btn-sm btn-outline-primary">Assign Vehicle</a>
                                @endif
                                <form method="POST" action="{{ route('superadmin.drivers.archive', $driver) }}" onsubmit="return confirm('Archive this driver? Historical incidents and reports will remain available.');">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Archive</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        {{ $archived ? 'No archived drivers found.' : 'No drivers found.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>

        </table>

    </div>

</div>

@endsection