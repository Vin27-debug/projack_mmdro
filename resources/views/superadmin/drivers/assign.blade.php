@extends('layouts.superadmin')

@section('content')

<div class="page-header">
    <div>
        <div class="small text-uppercase text-white-50 mb-2">
            Driver Management
        </div>

        <h1 class="page-title">
            Assign Vehicle
        </h1>

        <p class="page-subtitle mb-0">
            Assign an available vehicle to this driver.
        </p>
    </div>
</div>

<div class="card admin-card border-0 shadow-sm p-4">

    <div class="mb-4">
        <h5 class="fw-bold mb-1">
            {{ $driver->user->name }}
        </h5>

        <div class="text-muted">
            Badge ID: {{ $driver->badge_id }}
        </div>
    </div>

    {{-- CURRENT VEHICLE --}}
    @if($currentAssignment && $currentAssignment->ambulance)

        <div class="alert alert-info">
            <strong>Current Vehicle:</strong>
            {{ $currentAssignment->ambulance->vehicle_name }}

            <br>

            <small>
                {{ ucwords(str_replace('_', ' ', $currentAssignment->ambulance->vehicle_type)) }}
                —
                {{ $currentAssignment->ambulance->plate_number }}
            </small>
        </div>

    @else

        <div class="alert alert-warning">
            This driver currently has no vehicle assigned.
        </div>

    @endif


    {{-- VEHICLE SELECTION --}}
    <form
        method="POST"
        action="{{ route('superadmin.drivers.assign.store', $driver) }}">

        @csrf

        <div class="mb-4">

            <label class="form-label fw-semibold">
                Select Vehicle
            </label>

            <select
                name="ambulance_id"
                class="form-select"
                required>

                <option value="">
                    -- Select Vehicle --
                </option>

                @foreach($vehicles as $vehicle)

                    <option
                        value="{{ $vehicle->id }}"
                        {{ old('ambulance_id', $currentAssignment?->ambulance_id) == $vehicle->id ? 'selected' : '' }}>

                        {{ $vehicle->vehicle_name }}
                        —
                        {{ ucwords(str_replace('_', ' ', $vehicle->vehicle_type)) }}
                        ({{ $vehicle->plate_number }})

                    </option>

                @endforeach

            </select>

            @error('ambulance_id')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
            @enderror

        </div>


        {{-- NO VEHICLES --}}
        @if($vehicles->isEmpty())

            <div class="alert alert-warning">
                No available vehicles found.
            </div>

        @endif


        {{-- BUTTONS --}}
        <div class="d-flex justify-content-end gap-2">

            <a
                href="{{ route('superadmin.drivers') }}"
                class="btn btn-secondary">

                Cancel

            </a>

            <button
                type="submit"
                class="btn btn-primary"
                {{ $vehicles->isEmpty() ? 'disabled' : '' }}>

                Assign Vehicle

            </button>

        </div>

    </form>

</div>

@endsection