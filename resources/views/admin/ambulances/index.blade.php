@extends('layouts.admin')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Vehicle Management</h2>
            <p class="text-muted mb-0">
                Manage ambulances, fire trucks, police units, and rescue vehicles.
            </p>
        </div>

        <a href="{{ route('admin.ambulances.create') }}"
            class="btn btn-primary rounded-3 px-4">
            <i class="bi bi-plus-lg me-1"></i>
            Add Vehicle
        </a>
    </div>

    {{-- SUCCESS --}}
    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-3">
        <i class="bi bi-check-circle me-2"></i>
        {{ session('success') }}
    </div>
    @endif

    {{-- ERROR --}}
    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm rounded-3">
        <i class="bi bi-exclamation-circle me-2"></i>
        {{ session('error') }}
    </div>
    @endif

    {{-- VEHICLES --}}
    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-header bg-white border-0 p-4">
            <h5 class="fw-bold mb-1">
                Registered Vehicles
            </h5>

            <small class="text-muted">
                {{ $ambulances->count() }} vehicle(s) registered
            </small>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th class="px-4">Vehicle</th>
                            <th>Plate Number</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-end px-4">Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse($ambulances as $ambulance)

                        <tr>

                            {{-- VEHICLE --}}
                            <td class="px-4">

                                <div class="d-flex align-items-center gap-3">

                                    <div class="vehicle-icon">

                                        @switch($ambulance->vehicle_type)

                                        @case('ambulance')
                                        🚑
                                        @break

                                        @case('fire_truck')
                                        🚒
                                        @break

                                        @case('police')
                                        🚓
                                        @break

                                        @case('rescue_van')
                                        🚐
                                        @break

                                        @default
                                        🚗

                                        @endswitch

                                    </div>

                                    <div>

                                        <div class="fw-semibold">
                                            {{ $ambulance->vehicle_name }}
                                        </div>

                                        <small class="text-muted">
                                            Vehicle #{{ $ambulance->id }}
                                        </small>

                                    </div>

                                </div>

                            </td>

                            {{-- PLATE --}}
                            <td>
                                <span class="fw-semibold">
                                    {{ $ambulance->plate_number }}
                                </span>
                            </td>

                            {{-- TYPE --}}
                            <td>

                                @switch($ambulance->vehicle_type)

                                @case('ambulance')
                                Ambulance
                                @break

                                @case('fire_truck')
                                Fire Truck
                                @break

                                @case('police')
                                Police
                                @break

                                @case('rescue_van')
                                Rescue Van
                                @break

                                @default
                                {{ ucfirst($ambulance->vehicle_type) }}

                                @endswitch

                            </td>

                            {{-- STATUS --}}
                            <td>

                                @switch($ambulance->status)

                                @case('available')
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                                    Available
                                </span>
                                @break

                                @case('on_duty')
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
                                    On Duty
                                </span>
                                @break

                                @case('maintenance')
                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-2">
                                    Maintenance
                                </span>
                                @break

                                @default
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-2">
                                    {{ ucfirst($ambulance->status) }}
                                </span>

                                @endswitch

                            </td>

                            {{-- ACTIONS --}}
                            <td class="text-end px-4">

                                <a href="{{ route('admin.ambulances.edit', $ambulance) }}"
                                    class="btn btn-sm btn-outline-primary rounded-3 me-1">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form action="{{ route('admin.ambulances.destroy', $ambulance) }}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Are you sure you want to delete this vehicle?');">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        class="btn btn-sm btn-outline-danger rounded-3">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                </form>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="5" class="text-center py-5">

                                <div class="text-muted">

                                    <div class="fs-1 mb-2">
                                        🚑
                                    </div>

                                    <h6 class="fw-semibold">
                                        No vehicles registered
                                    </h6>

                                    <p class="small mb-3">
                                        Add your first ambulance, fire truck,
                                        police unit, or rescue vehicle.
                                    </p>

                                    <a href="{{ route('admin.ambulances.create') }}"
                                        class="btn btn-primary rounded-3">
                                        <i class="bi bi-plus-lg me-1"></i>
                                        Add Vehicle
                                    </a>

                                </div>

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<style>
    .vehicle-icon {
        width: 44px;
        height: 44px;

        display: flex;
        align-items: center;
        justify-content: center;

        background: #f1f5f9;

        border-radius: 12px;

        font-size: 22px;
    }

    .table> :not(caption)>*>* {
        padding-top: 15px;
        padding-bottom: 15px;
    }
</style>

@endsection