@extends('layouts.superadmin')

@section('content')

<div class="page-header d-flex justify-content-between align-items-center">

    <div>
        <div class="small text-uppercase text-white-50 mb-2">
            Driver Management
        </div>

        <h1 class="page-title">
            Approved Drivers
        </h1>

        <p class="page-subtitle mb-0">
            All drivers currently approved for dispatch and monitoring.
        </p>
    </div>

    <div>
        <a
            href="{{ route('superadmin.drivers.create') }}"
            class="btn btn-primary">
            + Create Driver
        </a>
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
                    <th>License</th>
                    <th>Assigned Vehicle</th>
                    <th>Action</th>
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

                    {{-- LICENSE --}}
                    <td>
                        {{ $driver->license_number ?? 'Not set' }}
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
                        <a
                            href="{{ route('superadmin.drivers.assign', $driver) }}"
                            class="btn btn-sm btn-outline-primary">
                            Assign Vehicle
                        </a>
                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        No approved drivers found.
                    </td>
                </tr>
                @endforelse
            </tbody>

        </table>

    </div>

</div>

@endsection