@extends('layouts.driver')

@section('content')

{{-- =====================================================
     LEAFLET CSS
===================================================== --}}
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

@if(session('success'))
<div class="alert alert-success rounded-4 border-0 shadow-sm">
    {{ session('success') }}
</div>
@endif

{{-- =====================================================
     DASHBOARD
===================================================== --}}

<div class="driver-dashboard-shell">

    {{-- =================================================
         HERO
    ================================================== --}}
    <div class="card border-0 shadow-sm rounded-4 hero-panel overflow-hidden mb-4">

        <div class="row g-0 align-items-stretch">

            <div class="col-12 col-lg-8 hero-panel-body">

                <div class="small text-uppercase fw-semibold hero-eyebrow">
                    Emergency Operations
                </div>

                <h2 class="fw-bold mb-2">
                    Driver Operations Center
                </h2>

                <p class="mb-3 mb-lg-4 hero-copy">
                    {{ $driver->user->name ?? 'Driver' }}
                    — monitor active dispatches and coordinate ambulance response with precision.
                </p>

                <div class="d-flex flex-wrap gap-2">

                    {{-- PANIC --}}
                    <button id="panicBtn"
                            type="button"
                            class="btn btn-danger btn-lg driver-action-btn">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        PANIC
                    </button>

                    {{-- HIJACK --}}
                    <button id="hijackBtn"
                            type="button"
                            class="btn btn-warning btn-lg driver-action-btn">
                        <i class="bi bi-shield-exclamation"></i>
                        HIJACK
                    </button>

                    {{-- REPORT --}}
                    @if($reportableDispatch)

                        <a href="{{ route('driver.report.create', $reportableDispatch->incident) }}"
                           class="btn btn-primary btn-lg driver-action-btn">
                            <i class="bi bi-file-earmark-text"></i>
                            File Report
                        </a>

                    @else

                        <button type="button"
                                class="btn btn-outline-light btn-lg driver-action-btn"
                                disabled>
                            No Report Available
                        </button>

                    @endif

                    {{-- LOGOUT --}}
                    <form method="POST"
                          action="{{ route('logout') }}"
                          class="d-inline">

                        @csrf

                        <button type="submit"
                                class="btn btn-outline-light btn-lg driver-action-btn">
                            <i class="bi bi-box-arrow-right"></i>
                            Logout
                        </button>

                    </form>

                </div>

            </div>

            {{-- HERO SIDE --}}
            <div class="col-12 col-lg-4 hero-side-panel">

                <div class="hero-summary-card">

                    <div class="d-flex align-items-center gap-3 mb-3">

                        <div class="hero-icon">
                            <i class="bi bi-truck-flatbed"></i>
                        </div>

                        <div>

                            <div class="small text-uppercase opacity-75">
                                Driver Status
                            </div>

                            @php
                                $status = $driver->status ?? 'available';

                                $statusClass = match($status) {
                                    'available' => 'bg-success',
                                    'assigned' => 'bg-primary',
                                    'accepted' => 'bg-info',
                                    'en_route' => 'bg-warning text-dark',
                                    'on_scene',
                                    'arrived' => 'bg-secondary',
                                    'offline' => 'bg-secondary',
                                    default => 'bg-dark',
                                };
                            @endphp

                            <span class="badge {{ $statusClass }} fs-6 px-3 py-2">
                                {{ strtoupper(str_replace('_', ' ', $status)) }}
                            </span>

                        </div>

                    </div>

                    <div>

                        <div class="small text-uppercase opacity-75">
                            Active Dispatch
                        </div>

                        <div class="fw-semibold">
                            {{ $currentDispatch?->incident?->incident_number ?? 'None' }}
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =================================================
         STAT CARDS
    ================================================== --}}

    <div class="row g-3 mb-4">

        {{-- ACTIVE DISPATCH --}}
        <div class="col-12 col-sm-6 col-lg-3">

            <div class="card border-0 shadow-sm rounded-4 stat-card h-100">

                <div class="card-body d-flex align-items-center gap-3">

                    <div class="stat-icon bg-primary text-white">
                        <i class="bi bi-activity"></i>
                    </div>

                    <div>

                        <div class="small text-muted">
                            Active Dispatch
                        </div>

                        <div class="fw-bold">
                            {{ $currentDispatch?->incident?->incident_number ?? '—' }}
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- VEHICLE --}}
        <div class="col-12 col-sm-6 col-lg-3">

            <div class="card border-0 shadow-sm rounded-4 stat-card h-100">

                <div class="card-body d-flex align-items-center gap-3">

                    <div class="stat-icon bg-info text-white">
                        <i class="bi bi-truck"></i>
                    </div>

                    <div>

                        <div class="small text-muted">
                            Vehicle
                        </div>

                        @php

                            $activeVehicle =
                                $currentDispatch?->vehicle
                                ?? $currentDispatch?->ambulance
                                ?? $driver->activeVehicleAssignment?->ambulance;

                            $vehicleLabel =
                                $activeVehicle?->vehicle_name
                                ?? 'Not Assigned';

                            $vehiclePlate =
                                $activeVehicle?->plate_number
                                ?? null;

                        @endphp

                        <div class="fw-bold">
                            {{ $vehicleLabel }}
                        </div>

                        @if($vehiclePlate)

                            <div class="small text-muted">
                                {{ $vehiclePlate }}
                            </div>

                        @endif

                    </div>

                </div>

            </div>

        </div>


        {{-- DRIVER STATUS --}}
        <div class="col-12 col-sm-6 col-lg-3">

            <div class="card border-0 shadow-sm rounded-4 stat-card h-100">

                <div class="card-body d-flex align-items-center gap-3">

                    <div class="stat-icon bg-success text-white">
                        <i class="bi bi-person-badge"></i>
                    </div>

                    <div>

                        <div class="small text-muted">
                            Driver Status
                        </div>

                        @php

                            $status = $driver->status ?? 'available';

                            $statusClass = match($status) {
                                'available' => 'bg-success',
                                'assigned' => 'bg-primary',
                                'accepted' => 'bg-info',
                                'en_route' => 'bg-warning text-dark',
                                'arrived',
                                'on_scene' => 'bg-secondary',
                                'offline' => 'bg-secondary',
                                default => 'bg-dark',
                            };

                        @endphp

                        <span class="badge {{ $statusClass }} fs-6 px-3 py-2">
                            {{ strtoupper(str_replace('_', ' ', $status)) }}
                        </span>

                    </div>

                </div>

            </div>

        </div>


        {{-- GPS --}}
        <div class="col-12 col-sm-6 col-lg-3">

            <div class="card border-0 shadow-sm rounded-4 stat-card h-100">

                <div class="card-body d-flex align-items-center gap-3">

                    <div class="stat-icon bg-secondary text-white">
                        <i class="bi bi-geo-alt"></i>
                    </div>

                    <div>

                        <div class="small text-muted">
                            GPS Status
                        </div>

                        <div id="gpsStatus"
                             class="fw-bold"
                             role="status"
                             aria-live="polite">
                            Starting...
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =================================================
         MAP + INCIDENT
    ================================================== --}}

    <div class="row g-4">

        <div class="col-12 col-xl-8">

            <div class="card border-0 shadow-sm rounded-4">

                <div class="card-body">

                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">

                        <div>

                            <h5 class="fw-bold mb-1">
                                Mission Map
                            </h5>

                            <p class="text-muted small mb-0">
                                Live position and incident location for rapid response.
                            </p>

                        </div>

                        <span class="badge rounded-pill bg-danger-subtle text-danger align-self-start">
                            Live Tracking
                        </span>

                    </div>


                    {{-- ASSIGNED VEHICLE --}}
                    @php

                        $assignedVehicle =
                            $driver->activeVehicleAssignment?->ambulance;

                        $assignedVehicleLabel =
                            $assignedVehicle
                            ? trim(
                                ($assignedVehicle->vehicle_name ?? '')
                                .
                                (
                                    $assignedVehicle->plate_number
                                    ? ' • ' . $assignedVehicle->plate_number
                                    : ''
                                )
                            )
                            : null;

                    @endphp


                    {{-- =================================================
                         ACTIVE DISPATCH
                    ================================================== --}}

                    @if($currentDispatch && $currentDispatch->incident)

                        <div class="map-shell">
                            <div id="map"></div>
                        </div>


                        {{-- INCIDENT NUMBER --}}
                        <div class="mb-3 p-3 rounded-3 bg-secondary bg-opacity-10">

                            <div class="small text-muted text-uppercase">
                                Incident #
                            </div>

                            <div class="fw-semibold">
                                {{ $currentDispatch->incident->incident_number }}
                            </div>

                        </div>


                        {{-- LOCATION --}}
                        <div class="mb-3">

                            <div class="small text-muted text-uppercase">
                                Location
                            </div>

                            <div class="fw-semibold">
                                {{ $currentDispatch->incident->location }}
                            </div>

                        </div>


                        {{-- VEHICLE --}}
                        <div class="mb-3">

                            <div class="small text-muted text-uppercase">
                                Vehicle
                            </div>

                            <div class="fw-semibold">

                                {{
                                    $currentDispatch->vehicle?->vehicle_name
                                    ??
                                    $currentDispatch->ambulance?->vehicle_name
                                    ??
                                    $assignedVehicleLabel
                                    ??
                                    'Not assigned'
                                }}

                            </div>

                        </div>


                        {{-- STATUS --}}
                        <div class="mb-3">

                            <div class="small text-muted text-uppercase">
                                Status
                            </div>

                            @php

                                $s = $currentDispatch->status ?? 'pending';

                                $statusClass = match($s) {

                                    'assigned'
                                        => 'badge bg-primary',

                                    'accepted'
                                        => 'badge bg-success',

                                    'en_route'
                                        => 'badge bg-warning text-dark',

                                    'arrived'
                                        => 'badge bg-info text-dark',

                                    'completed'
                                        => 'badge bg-dark',

                                    default
                                        => 'badge bg-secondary',

                                };

                            @endphp

                            <div class="mt-1">

                                <span class="{{ $statusClass }}">
                                    {{ str_replace('_', ' ', ucfirst($s)) }}
                                </span>

                            </div>

                        </div>


                        {{-- =================================================
                             ACTION BUTTONS
                        ================================================== --}}

                        <div class="d-flex flex-column flex-sm-row gap-2 flex-wrap">

                            {{-- ACCEPT / DECLINE --}}
                            @if(
                                in_array(
                                    $currentDispatch->status,
                                    [
                                        \App\Models\Dispatch::STATUS_PENDING,
                                        \App\Models\Dispatch::STATUS_ASSIGNED
                                    ],
                                    true
                                )
                            )

                                <form method="POST"
                                      action="{{ route('driver.dispatch.accept', $currentDispatch) }}"
                                      class="flex-fill">

                                    @csrf

                                    <button type="submit"
                                            class="btn btn-primary w-100 btn-lg driver-action-btn">
                                        <i class="bi bi-check-circle"></i>
                                        Accept Dispatch
                                    </button>

                                </form>


                                <form method="POST"
                                      action="{{ route('driver.dispatch.decline', $currentDispatch) }}"
                                      class="flex-fill">

                                    @csrf

                                    <button type="submit"
                                            class="btn btn-outline-danger w-100 btn-lg driver-action-btn">
                                        <i class="bi bi-x-circle"></i>
                                        Decline Dispatch
                                    </button>

                                </form>


                            {{-- ACCEPTED --}}
                            @elseif(
                                $currentDispatch->status
                                === \App\Models\Dispatch::STATUS_ACCEPTED
                            )

                                <form method="POST"
                                      action="{{ route('driver.incidents.en-route', $currentDispatch->incident) }}"
                                      class="flex-fill">

                                    @csrf

                                    <button type="submit"
                                            class="btn btn-primary w-100 btn-lg driver-action-btn">
                                        <i class="bi bi-signpost-2"></i>
                                        Mark En Route
                                    </button>

                                </form>


                            {{-- EN ROUTE --}}
                            @elseif(
                                $currentDispatch->status
                                === \App\Models\Dispatch::STATUS_EN_ROUTE
                            )

                                <form method="POST"
                                      action="{{ route('driver.incidents.arrived', $currentDispatch->incident) }}"
                                      class="flex-fill">

                                    @csrf

                                    <button type="submit"
                                            class="btn btn-warning w-100 btn-lg driver-action-btn">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        Mark On Scene
                                    </button>

                                </form>


                            {{-- ARRIVED --}}
                            @elseif(
                                $currentDispatch->status
                                === \App\Models\Dispatch::STATUS_ARRIVED
                            )

                                <form method="POST"
                                      action="{{ route('driver.incidents.completed', $currentDispatch->incident) }}"
                                      class="flex-fill">

                                    @csrf

                                    <button type="submit"
                                            class="btn btn-success w-100 btn-lg driver-action-btn">
                                        <i class="bi bi-check2-all"></i>
                                        Mark Completed
                                    </button>

                                </form>

                            @endif


                            {{-- ASSIGNMENT --}}
                            <a href="{{ route('driver.assignment') }}"
                               class="btn btn-outline-primary btn-lg driver-action-btn flex-fill">
                                <i class="bi bi-list-task"></i>
                                View Assignment
                            </a>

                        </div>


                    {{-- =================================================
                         REPORTABLE DISPATCH
                    ================================================== --}}

                    @elseif(isset($reportableDispatch) && $reportableDispatch?->incident)

                        <div class="map-shell">
                            <div id="map"></div>
                        </div>


                        <div class="mb-3 p-3 rounded-3 bg-secondary bg-opacity-10">

                            <div class="small text-muted text-uppercase">
                                Incident #
                            </div>

                            <div class="fw-semibold">
                                {{ $reportableDispatch->incident->incident_number }}
                            </div>

                        </div>


                        <div class="mb-3">

                            <div class="small text-muted text-uppercase">
                                Location
                            </div>

                            <div class="fw-semibold">
                                {{ $reportableDispatch->incident->location }}
                            </div>

                        </div>


                        <div class="mb-3">

                            <div class="small text-muted text-uppercase">
                                Vehicle
                            </div>

                            <div class="fw-semibold">

                                {{
                                    $reportableDispatch->vehicle?->vehicle_name
                                    ??
                                    $reportableDispatch->ambulance?->vehicle_name
                                    ??
                                    $assignedVehicle?->vehicle_name
                                    ??
                                    'Not assigned'
                                }}

                            </div>

                        </div>


                        <div class="mb-3">

                            <div class="small text-muted text-uppercase">
                                Status
                            </div>

                            <div class="mt-1">

                                <span class="badge bg-dark">
                                    Completed
                                </span>

                            </div>

                        </div>


                        <div class="d-flex flex-column flex-sm-row gap-2 flex-wrap">

                            <a href="{{ route('driver.report.create', $reportableDispatch->incident) }}"
                               class="btn btn-outline-primary btn-lg driver-action-btn flex-fill">

                                <i class="bi bi-file-earmark-text"></i>
                                Submit Report

                            </a>

                        </div>


                    {{-- =================================================
                         NO DISPATCH
                    ================================================== --}}

                    @else

                        <div class="map-shell">
                            <div id="map"></div>
                        </div>


                        <div class="mb-3 p-3 rounded-3 bg-secondary bg-opacity-10">

                            <div class="small text-muted text-uppercase">
                                Vehicle
                            </div>

                            <div class="fw-semibold">
                                {{ $assignedVehicleLabel ?? 'Not assigned' }}
                            </div>

                        </div>


                        <div class="mb-3">

                            <div class="small text-muted text-uppercase">
                                Status
                            </div>

                            <div class="mt-1">

                                <span class="badge bg-secondary">
                                    No Active Dispatch
                                </span>

                            </div>

                        </div>

                    @endif

                </div>

            </div>


            {{-- =================================================
                 ASSIGNED INCIDENTS
            ================================================== --}}

            <div class="card border-0 shadow-sm rounded-4 mt-4">

                <div class="card-body">

                    <h6 class="fw-bold mb-2">
                        Assigned Incidents
                    </h6>

                    <p class="small text-muted mb-3">
                        Recent assignments and activity for this driver.
                    </p>


                    <div class="table-responsive">

                        <table class="table table-sm align-middle mb-0">

                            <thead>

                                <tr>

                                    <th>
                                        Incident
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @foreach($incidents->take(5) as $incident)

                                    <tr>

                                        <td>

                                            <div class="fw-semibold">
                                                {{ $incident->incident_number }}
                                            </div>

                                            <div class="small text-muted">
                                                {{ $incident->location }}
                                            </div>

                                        </td>

                                        <td>

                                            <span class="badge bg-secondary text-white">
                                                {{ str_replace('_', ' ', ucfirst($incident->status)) }}
                                            </span>

                                        </td>

                                    </tr>

                                @endforeach


                                @if($incidents->isEmpty())

                                    <tr>

                                        <td colspan="2"
                                            class="text-center text-muted py-3">

                                            No recent activity

                                        </td>

                                    </tr>

                                @endif

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =====================================================
     CSS
===================================================== --}}

<style>

body {
    padding-bottom: 80px;
}

@media (min-width: 992px) {
    body {
        padding-bottom: 0;
    }
}

.driver-dashboard-shell {
    display: block;
    padding: .75rem;
}


/* =====================================================
   HERO
===================================================== */

.hero-panel {
    background: linear-gradient(
        90deg,
        #06243a 0%,
        #083b57 60%
    );

    color: #f6f8fb;
    border: none !important;
}

.hero-panel-body {
    padding: 1rem;
}

.hero-side-panel {
    padding: 1rem;
    background: rgba(255,255,255,.08);
    margin-top: 1rem;
}

.hero-eyebrow {
    letter-spacing: .16em;
    opacity: .78;
    font-size: .7rem;
    font-weight: 700;
}

.hero-copy {
    color: rgba(255,255,255,.82);
    line-height: 1.6;
    font-size: .9rem;
}

.hero-summary-card {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 1rem;
    padding: 1rem;
    height: 100%;
}

.hero-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: rgba(255,255,255,.2);
    font-size: 1rem;
    flex-shrink: 0;
    color: #fff;
}

.hero-panel h2 {
    font-size: 1.25rem;
    margin-bottom: .5rem;
    font-weight: 700;
    color: #f8fafc;
}


/* =====================================================
   BUTTONS
===================================================== */

.driver-action-btn {
    min-height: 46px;
    border-radius: 999px;
    padding: .6rem 1rem;
    font-size: .95rem;
    font-weight: 600;
    white-space: nowrap;
    transition: all .2s ease;
    touch-action: manipulation;
}

.btn {
    min-height: 44px;
    touch-action: manipulation;
    font-weight: 600;
}

.btn-lg {
    min-height: 46px;
    font-size: .95rem;
    padding: .6rem 1rem;
}

.btn-danger {
    background: linear-gradient(
        135deg,
        #dc3545 0%,
        #c82333 100%
    ) !important;

    border: none !important;
}

.btn-warning {
    background: linear-gradient(
        135deg,
        #ffc107 0%,
        #ffb300 100%
    ) !important;

    border: none !important;
    color: #000 !important;
}

.btn-outline-light {
    color: #f8fafc !important;
    border-color: rgba(255,255,255,.3) !important;
}


/* =====================================================
   STAT CARDS
===================================================== */

.stat-card {
    border: 1px solid rgba(0,0,0,.06);
    margin-bottom: .75rem;

    background: rgba(
        7,
        18,
        38,
        .92
    ) !important;

    transition: all .3s ease;
}

.stat-card:hover {
    box-shadow:
        0 4px 12px rgba(
            59,
            105,
            255,
            .15
        ) !important;

    transform: translateY(-2px);
}

.stat-card .card-body {
    padding: 1rem;
}

.stat-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.stat-card .small {
    font-size: .75rem;
    color: rgba(255,255,255,.7);
}

.stat-card .fw-bold {
    font-size: .95rem;
    color: #eef4ff;
}


/* =====================================================
   MAP
===================================================== */

.map-shell {
    min-height: 260px;
    border-radius: 1rem;
    overflow: hidden;
    border: 1px solid rgba(0,0,0,.06);
    margin-bottom: 1rem;
    background: linear-gradient(
        135deg,
        rgba(59,105,255,.1),
        rgba(0,0,0,.2)
    );
}

#map {
    width: 100%;
    height: 260px;
}


/* =====================================================
   GPS
===================================================== */

#gpsStatus {
    font-size: .85rem;
    font-weight: 600;
    display: inline-block;
    padding: .3rem .75rem;
    border-radius: .5rem;
    background: rgba(255,255,255,.1);
    color: #eef4ff;
}

#gpsStatus.status-live {
    background: rgba(76,175,80,.2);
    color: #4CAF50;
}

#gpsStatus.status-offline {
    background: rgba(244,67,54,.2);
    color: #F44336;
}

#gpsStatus.status-permission {
    background: rgba(255,152,0,.2);
    color: #FF9800;
}

#gpsStatus.status-unavailable {
    background: rgba(158,158,158,.2);
    color: #9E9E9E;
}


/* =====================================================
   CARDS
===================================================== */

.card {
    background: rgba(
        7,
        18,
        38,
        .92
    ) !important;

    border: 1px solid rgba(
        255,
        255,
        255,
        .08
    ) !important;

    margin-bottom: 1rem;
    color: #eef4ff;
}

.card-body {
    color: #eef4ff;
}

h5.fw-bold {
    font-size: 1rem;
    color: #eef4ff;
    font-weight: 700;
}

.text-muted {
    color: rgba(
        255,
        255,
        255,
        .72
    ) !important;
}


/* =====================================================
   TABLE
===================================================== */

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table-responsive table {
    font-size: .8rem;
    background: rgba(
        7,
        18,
        38,
        .92
    ) !important;
}

.table-responsive thead th {
    background: rgba(
        11,
        26,
        49,
        .92
    ) !important;

    color: rgba(
        255,
        255,
        255,
        .72
    );

    border-color: rgba(
        255,
        255,
        255,
        .08
    ) !important;
}

.table-responsive tbody td {
    background: rgba(
        7,
        18,
        38,
        .92
    ) !important;

    border-color: rgba(
        255,
        255,
        255,
        .08
    ) !important;

    color: #eef4ff;
}

.table-responsive th,
.table-responsive td {
    padding: .6rem .5rem;
}


/* =====================================================
   BADGES
===================================================== */

.badge {
    font-weight: 600;
    font-size: .8rem;
    padding: .35rem .6rem !important;
}

.badge.bg-success {
    background: linear-gradient(
        135deg,
        #20c997,
        #17a2b8
    ) !important;
}

.badge.bg-danger {
    background: linear-gradient(
        135deg,
        #dc3545,
        #c82333
    ) !important;
}

.badge.bg-warning {
    background: linear-gradient(
        135deg,
        #ffc107,
        #ffb300
    ) !important;

    color: #000 !important;
}

.badge.bg-info {
    background: linear-gradient(
        135deg,
        #17a2b8,
        #138496
    ) !important;
}

.badge.bg-primary {
    background: linear-gradient(
        135deg,
        #0d6efd,
        #0a58ca
    ) !important;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 425px) {

    .driver-dashboard-shell {
        padding: .5rem;
    }

    .hero-panel-body {
        padding: .75rem;
    }

    .hero-side-panel {
        padding: .75rem;
        margin-top: .75rem;
    }

    .hero-panel h2 {
        font-size: 1.1rem;
    }

    .hero-copy {
        font-size: .85rem;
    }

    .driver-action-btn {
        min-height: 40px;
        font-size: .85rem;
        padding: .4rem .75rem;
    }

    .map-shell {
        min-height: 220px;
    }

    #map {
        height: 220px;
    }

    .btn-lg {
        min-height: 42px;
        font-size: .85rem;
        padding: .4rem .75rem;
    }
}


@media (min-width: 576px) {

    .driver-dashboard-shell {
        padding: 1rem;
    }

    body {
        padding-bottom: 0;
    }

    .hero-panel-body {
        padding: 1.25rem;
    }

    .hero-side-panel {
        padding: 1.25rem;
        margin-top: 0;
    }

    .hero-panel h2 {
        font-size: 1.4rem;
    }

    .map-shell {
        min-height: 320px;
    }

    #map {
        height: 320px;
    }

}


@media (min-width: 768px) {

    .hero-panel-body {
        padding: 1.75rem;
    }

    .hero-side-panel {
        padding: 1.75rem;
    }

    .hero-panel h2 {
        font-size: 1.6rem;
    }

    .map-shell {
        min-height: 400px;
    }

    #map {
        height: 400px;
    }

    .stat-card .card-body {
        padding: 1.5rem;
    }

    .driver-action-btn {
        min-height: 48px;
        font-size: 1rem;
        padding: .75rem 1.25rem;
    }

}


@media (min-width: 992px) {

    body {
        padding-bottom: 0 !important;
    }

    .driver-dashboard-shell {
        padding: 1.5rem;
    }

    .hero-panel-body {
        padding: 2rem;
    }

    .hero-side-panel {
        padding: 2rem;
    }

    .hero-panel h2 {
        font-size: 1.75rem;
    }

    .map-shell {
        min-height: 450px;
    }

    #map {
        height: 450px;
    }

}


@media (min-width: 1200px) {

    .map-shell {
        min-height: 500px;
    }

    #map {
        height: 500px;
    }

}

</style>


{{-- =====================================================
     LEAFLET JS
===================================================== --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<script>

document.addEventListener('DOMContentLoaded', function () {

    /* =====================================================
       VARIABLES
    ===================================================== */

    let gpsIntervalId = null;

    let isPageVisible = true;

    let map = null;

    let driverMarker = null;

    let incidentMarker = null;


    /* =====================================================
       CSRF TOKEN
    ===================================================== */

    const csrfToken = '{{ csrf_token() }}';


    /* =====================================================
       INCIDENT LOCATION
    ===================================================== */

    const incidentLat = @if(
        $currentDispatch &&
        $currentDispatch->incident &&
        $currentDispatch->incident->latitude !== null
    )
        {{ (float) $currentDispatch->incident->latitude }}
    @else
        null
    @endif;

    const incidentLng = @if(
        $currentDispatch &&
        $currentDispatch->incident &&
        $currentDispatch->incident->longitude !== null
    )
        {{ (float) $currentDispatch->incident->longitude }}
    @else
        null
    @endif;


    /* =====================================================
       MAP INITIALIZATION
    ===================================================== */

    function initMap() {

        const mapElement = document.getElementById('map');

        if (!mapElement) {
            console.warn('Map element not found.');
            return;
        }

        if (typeof L === 'undefined') {
            console.error('Leaflet is not loaded.');
            return;
        }

        map = L.map('map').setView(
            [15.9800, 120.5700],
            13
        );


        L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                maxZoom: 19,
                attribution:
                    '&copy; OpenStreetMap contributors'
            }
        ).addTo(map);


        /* =============================================
           INCIDENT MARKER
        ============================================== */

        if (
            incidentLat !== null &&
            incidentLng !== null &&
            !isNaN(incidentLat) &&
            !isNaN(incidentLng)
        ) {

            incidentMarker = L.marker(
                [incidentLat, incidentLng],
                {
                    title: 'Incident Location'
                }
            )
            .addTo(map)
            .bindPopup('Incident Location');

        }

    }


    /* =====================================================
       GET GPS POSITION
    ===================================================== */

    function getPosition() {

        return new Promise(function (resolve, reject) {

            if (!navigator.geolocation) {

                reject({
                    code: 2,
                    message: 'Geolocation is not supported.'
                });

                return;
            }


            navigator.geolocation.getCurrentPosition(

                function (position) {

                    resolve({
                        latitude:
                            position.coords.latitude,

                        longitude:
                            position.coords.longitude,

                        accuracy:
                            position.coords.accuracy
                    });

                },

                function (error) {

                    reject(error);

                },

                {
                    enableHighAccuracy: true,

                    timeout: 10000,

                    maximumAge: 5000
                }

            );

        });

    }


    /* =====================================================
       UPDATE GPS STATUS
    ===================================================== */

    function updateGPSStatus(status) {

        const gpsEl =
            document.getElementById('gpsStatus');

        if (!gpsEl) {
            return;
        }


        const statusMap = {

            live:
                '🟢 Live Tracking',

            offline:
                '🔴 Offline',

            permission:
                '🟠 Permission Denied',

            unavailable:
                '⚪ Position Unavailable'

        };


        gpsEl.textContent =
            statusMap[status] || 'Unknown';


        gpsEl.className =
            'fw-bold status-' + status;

    }


    /* =====================================================
       UPDATE MAP DRIVER MARKER
    ===================================================== */

    function updateDriverMarker(
        latitude,
        longitude
    ) {

        if (!map) {
            return;
        }


        const driverPosition =
            [latitude, longitude];


        /* =============================================
           CREATE DRIVER MARKER
        ============================================== */

        if (!driverMarker) {

            driverMarker =
                L.marker(
                    driverPosition,
                    {
                        title: 'Your Position'
                    }
                )
                .addTo(map)
                .bindPopup('Your Current Position');

        }

        else {

            driverMarker.setLatLng(
                driverPosition
            );

        }


        /* =============================================
           FIT MAP
        ============================================== */

        if (
            incidentMarker &&
            driverMarker
        ) {

            const group =
                L.featureGroup([
                    driverMarker,
                    incidentMarker
                ]);


            map.fitBounds(
                group.getBounds(),
                {
                    padding: [50, 50]
                }
            );

        }

        else {

            map.setView(
                driverPosition,
                15
            );

        }

    }


    /* =====================================================
       SEND GPS LOCATION TO SERVER
    ===================================================== */

    async function sendLocation() {

        if (!isPageVisible) {
            return;
        }


        try {

            /* =============================================
               GET BROWSER GPS
            ============================================== */

            const coords =
                await getPosition();


            /* =============================================
               SEND TO LARAVEL
            ============================================== */

            const response =
                await fetch(
                    '{{ route("driver.gps.update") }}',
                    {
                        method: 'POST',

                        headers: {

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'Accept':
                                'application/json'

                        },

                        body: JSON.stringify({

                            latitude:
                                coords.latitude,

                            longitude:
                                coords.longitude,

                            accuracy:
                                coords.accuracy

                        })

                    }
                );


            if (!response.ok) {

                throw new Error(
                    'GPS update failed: HTTP ' +
                    response.status
                );

            }


            /* =============================================
               SERVER RESPONSE
            ============================================== */

            let data = null;

            try {

                data =
                    await response.json();

            }

            catch (jsonError) {

                console.warn(
                    'GPS endpoint returned no JSON response.'
                );

            }


            console.log(
                'GPS updated:',
                data
            );


            /* =============================================
               UI
            ============================================== */

            updateGPSStatus('live');


            /* =============================================
               MAP
            ============================================== */

            updateDriverMarker(
                coords.latitude,
                coords.longitude
            );

        }


        catch (error) {

            console.error(
                'GPS Error:',
                error
            );


            if (
                error &&
                error.code === 1
            ) {

                updateGPSStatus(
                    'permission'
                );

            }

            else if (
                error &&
                error.code === 2
            ) {

                updateGPSStatus(
                    'unavailable'
                );

            }

            else if (
                error &&
                error.code === 3
            ) {

                updateGPSStatus(
                    'offline'
                );

            }

            else {

                updateGPSStatus(
                    'offline'
                );

            }

        }

    }


    /* =====================================================
       START GPS
    ===================================================== */

    function startGPS() {

        if (!isPageVisible) {
            return;
        }


        /* =============================================
           SEND IMMEDIATELY
        ============================================== */

        sendLocation();


        /* =============================================
           CLEAR OLD INTERVAL
        ============================================== */

        if (gpsIntervalId) {

            clearInterval(
                gpsIntervalId
            );

        }


        /* =============================================
           SEND EVERY 15 SECONDS
        ============================================== */

        gpsIntervalId =
            setInterval(
                function () {

                    if (isPageVisible) {

                        sendLocation();

                    }

                },
                15000
            );

    }


    /* =====================================================
       STOP GPS
    ===================================================== */

    function stopGPS() {

        if (gpsIntervalId) {

            clearInterval(
                gpsIntervalId
            );

            gpsIntervalId = null;

        }

    }


    /* =====================================================
       PAGE VISIBILITY
    ===================================================== */

    document.addEventListener(
        'visibilitychange',
        function () {

            isPageVisible =
                !document.hidden;


            if (!isPageVisible) {

                stopGPS();

            }

            else {

                startGPS();

            }

        }
    );


    /* =====================================================
       PAGE HIDE
    ===================================================== */

    window.addEventListener(
        'pagehide',
        function () {

            stopGPS();

        }
    );


    /* =====================================================
       PANIC BUTTON
    ===================================================== */

    const panicBtn =
        document.getElementById(
            'panicBtn'
        );


    if (panicBtn) {

        panicBtn.addEventListener(
            'click',
            function () {

                triggerEmergency(
                    '{{ route("driver.panic.trigger") }}',
                    'PANIC'
                );

            }
        );

    }


    /* =====================================================
       HIJACK BUTTON
    ===================================================== */

    const hijackBtn =
        document.getElementById(
            'hijackBtn'
        );


    if (hijackBtn) {

        hijackBtn.addEventListener(
            'click',
            function () {

                triggerEmergency(
                    '{{ route("driver.hijack.trigger") }}',
                    'HIJACK'
                );

            }
        );

    }


    /* =====================================================
       EMERGENCY TRIGGER
    ===================================================== */

    async function triggerEmergency(
        url,
        label
    ) {

        const confirmed =
            confirm(
                `Trigger ${label} alert? This will notify dispatch immediately.`
            );


        if (!confirmed) {
            return;
        }


        try {

            const response =
                await fetch(
                    url,
                    {
                        method: 'POST',

                        headers: {

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'Accept':
                                'application/json'

                        }

                    }
                );


            if (!response.ok) {

                throw new Error(
                    'HTTP ' +
                    response.status
                );

            }


            let data = null;


            try {

                data =
                    await response.json();

            }

            catch (e) {

                console.log(
                    'Emergency request completed.'
                );

            }


            console.log(
                label +
                ' response:',
                data
            );


            alert(
                `${label} alert sent successfully!`
            );

        }


        catch (error) {

            console.error(
                `${label} error:`,
                error
            );


            alert(
                `Failed to send ${label} alert. Please try again.`
            );

        }

    }


    /* =====================================================
       INITIALIZE
    ===================================================== */

    initMap();

    startGPS();

});

</script>

@endsection