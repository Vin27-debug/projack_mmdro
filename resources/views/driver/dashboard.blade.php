@extends('layouts.driver')

@section('title', 'MuniResQ Driver Dashboard')

@section('content')

@php

/*
|--------------------------------------------------------------------------
| DRIVER
|--------------------------------------------------------------------------
*/

$driverName =
$driver->user->name
?? auth()->user()->name
?? 'Driver';


/*
|--------------------------------------------------------------------------
| VEHICLE
|--------------------------------------------------------------------------
*/

$assignedVehicle =
$currentDispatch?->vehicle
?? $currentDispatch?->ambulance
?? data_get($driver, 'activeVehicleAssignment.ambulance');


$vehicleName =
$assignedVehicle?->vehicle_name
?? 'Not Assigned';


$vehiclePlate =
$assignedVehicle?->plate_number
?? null;


/*
|--------------------------------------------------------------------------
| DRIVER STATUS
|--------------------------------------------------------------------------
*/

$driverStatus =
$driver->status
?? 'available';


$driverStatusClass = match ($driverStatus) {

'available'
=> 'bg-success',

'assigned'
=> 'bg-primary',

'accepted'
=> 'bg-info text-dark',

'en_route'
=> 'bg-warning text-dark',

'arrived',
'on_scene'
=> 'bg-secondary',

'offline'
=> 'bg-secondary',

default
=> 'bg-dark',
};


/*
|--------------------------------------------------------------------------
| CURRENT DISPATCH
|--------------------------------------------------------------------------
*/

$dispatchStatus =
$currentDispatch?->status;


/*
|--------------------------------------------------------------------------
| INCIDENT
|--------------------------------------------------------------------------
*/

$mapIncident =
$currentDispatch?->incident
?? ($reportableDispatch ?? null)?->incident;


$incidentLat =
$mapIncident?->latitude;


$incidentLng =
$mapIncident?->longitude;


$hasIncidentCoordinates =
$incidentLat !== null
&&
$incidentLng !== null
&&
is_numeric($incidentLat)
&&
is_numeric($incidentLng);

@endphp


{{-- =========================================================
     SUCCESS
========================================================== --}}

@if(session('success'))

<div
    class="alert alert-success
               border-0 rounded-4 shadow-sm">

    <i class="bi bi-check-circle me-2"></i>

    {{ session('success') }}

</div>

@endif


{{-- =========================================================
     ERROR
========================================================== --}}

@if(session('error'))

<div
    class="alert alert-danger
               border-0 rounded-4 shadow-sm">

    <i class="bi bi-exclamation-circle me-2"></i>

    {{ session('error') }}

</div>

@endif


{{-- VALIDATION --}}
@if(($errors ?? new \Illuminate\Support\ViewErrorBag())->any())

<div
    class="alert alert-danger
               border-0 rounded-4 shadow-sm">

    <strong>
        Please check the following:
    </strong>

    <ul class="mb-0 mt-2">

        @foreach(($errors ?? new \Illuminate\Support\ViewErrorBag())->all() as $error)

        <li>
            {{ $error }}
        </li>

        @endforeach

    </ul>

</div>

@endif


<div class="driver-dashboard-shell">


    {{-- =========================================================
         HERO
    ========================================================== --}}

    <section
        class="hero-panel card
               border-0 shadow-sm
               rounded-4 overflow-hidden
               mb-4">

        <div class="row g-0">


            {{-- HERO LEFT --}}
            <div
                class="col-12 col-lg-8
                       hero-panel-body">

                <div class="hero-eyebrow">
                    EMERGENCY OPERATIONS
                </div>


                <h1 class="hero-title">

                    Driver Operations Center

                </h1>


                <p class="hero-copy">

                    {{ $driverName }}

                    —
                    monitor dispatches,
                    update your response status,
                    and coordinate ambulance operations.

                </p>


                <div
                    class="d-flex
                           flex-wrap
                           gap-2">


                    {{-- PANIC --}}
                    <button
                        id="panicBtn"
                        type="button"
                        class="btn btn-danger
                               driver-action-btn">

                        <i
                            class="bi bi-exclamation-triangle-fill me-1">
                        </i>

                        PANIC

                    </button>


                    {{-- HIJACK --}}
                    <button
                        id="hijackBtn"
                        type="button"
                        class="btn btn-warning
                               driver-action-btn">

                        <i
                            class="bi bi-shield-exclamation me-1">
                        </i>

                        HIJACK

                    </button>


                    {{-- REPORT --}}
                    @if(
                    isset($reportableDispatch)
                    &&
                    $reportableDispatch?->incident
                    )

                    <a
                        href="{{ route(
                                'driver.report.create',
                                $reportableDispatch->incident
                            ) }}"
                        class="btn btn-primary
                                   driver-action-btn">

                        <i
                            class="bi bi-file-earmark-text me-1">
                        </i>

                        File Report

                    </a>

                    @else

                    <button
                        type="button"
                        class="btn btn-outline-light
                                   driver-action-btn"
                        disabled>

                        <i
                            class="bi bi-file-earmark-x me-1">
                        </i>

                        No Report Available

                    </button>

                    @endif


                    {{-- LOGOUT --}}
                    <form
                        method="POST"
                        action="{{ route('logout') }}">

                        @csrf

                        <button
                            type="submit"
                            class="btn btn-outline-light
                                   driver-action-btn">

                            <i
                                class="bi bi-box-arrow-right me-1">
                            </i>

                            Logout

                        </button>

                    </form>

                </div>

            </div>


            {{-- HERO RIGHT --}}
            <div
                class="col-12 col-lg-4
                       hero-side-panel">

                <div
                    class="hero-summary-card">


                    <div
                        class="d-flex
                               align-items-center
                               gap-3 mb-4">

                        <div class="hero-icon">

                            <i class="bi bi-truck"></i>

                        </div>


                        <div>

                            <div
                                class="small
                                       text-uppercase
                                       opacity-75">

                                Driver Status

                            </div>


                            <span
                                class="badge
                                       {{ $driverStatusClass }}
                                       fs-6 px-3 py-2">

                                {{ strtoupper(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $driverStatus
                                    )
                                ) }}

                            </span>

                        </div>

                    </div>


                    <div>

                        <div
                            class="small
                                   text-uppercase
                                   opacity-75">

                            Active Dispatch

                        </div>


                        <div
                            class="fw-semibold
                                   fs-5">

                            {{
                                $currentDispatch
                                ?->incident
                                ?->incident_number
                                ?? 'None'
                            }}

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
         STAT CARDS
    ========================================================== --}}

    <div class="row g-3 mb-4">


        {{-- ACTIVE DISPATCH --}}
        <div class="col-12 col-sm-6 col-xl-3">

            <div
                class="card stat-card h-100">

                <div
                    class="card-body
                           d-flex
                           align-items-center
                           gap-3">

                    <div
                        class="stat-icon bg-primary">

                        <i class="bi bi-activity"></i>

                    </div>


                    <div class="min-w-0">

                        <div
                            class="small text-muted">

                            Active Dispatch

                        </div>


                        <div
                            class="stat-value
                                   text-truncate">

                            {{
                                $currentDispatch
                                ?->incident
                                ?->incident_number
                                ?? '—'
                            }}

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- VEHICLE --}}
        <div class="col-12 col-sm-6 col-xl-3">

            <div
                class="card stat-card h-100">

                <div
                    class="card-body
                           d-flex
                           align-items-center
                           gap-3">

                    <div
                        class="stat-icon bg-info">

                        <i class="bi bi-truck"></i>

                    </div>


                    <div class="min-w-0">

                        <div
                            class="small text-muted">

                            Vehicle

                        </div>


                        <div
                            class="stat-value
                                   text-truncate">

                            {{ $vehicleName }}

                        </div>


                        @if($vehiclePlate)

                        <div
                            class="small text-muted
                                       text-truncate">

                            {{ $vehiclePlate }}

                        </div>

                        @endif

                    </div>

                </div>

            </div>

        </div>


        {{-- DRIVER STATUS --}}
        <div class="col-12 col-sm-6 col-xl-3">

            <div
                class="card stat-card h-100">

                <div
                    class="card-body
                           d-flex
                           align-items-center
                           gap-3">

                    <div
                        class="stat-icon bg-success">

                        <i
                            class="bi bi-person-badge">
                        </i>

                    </div>


                    <div>

                        <div
                            class="small text-muted">

                            Driver Status

                        </div>


                        <span
                            class="badge
                                   {{ $driverStatusClass }}">

                            {{ strtoupper(
                                str_replace(
                                    '_',
                                    ' ',
                                    $driverStatus
                                )
                            ) }}

                        </span>

                    </div>

                </div>

            </div>

        </div>


        {{-- GPS --}}
        <div class="col-12 col-sm-6 col-xl-3">

            <div
                class="card stat-card h-100">

                <div
                    class="card-body
                           d-flex
                           align-items-center
                           gap-3">

                    <div
                        class="stat-icon bg-secondary">

                        <i class="bi bi-geo-alt"></i>

                    </div>


                    <div>

                        <div
                            class="small text-muted">

                            GPS Status

                        </div>


                        <div
                            id="gpsStatus"
                            class="gps-status
                                   status-waiting"
                            role="status"
                            aria-live="polite">

                            Waiting for GPS...

                        </div>

                        <div id="currentSpeed" class="small text-muted">Speed: unavailable | Speed limit unavailable | UNRATED</div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         MAIN ROW
    ========================================================== --}}

    <div class="row g-4">


        {{-- =====================================================
             LEFT
        ====================================================== --}}

        <div class="col-12 col-xl-8">


            {{-- =================================================
                 MISSION MAP
            ================================================== --}}

            <div
                class="card
                       rounded-4
                       shadow-sm">

                <div class="card-body">


                    <div
                        class="d-flex
                               flex-column
                               flex-md-row
                               justify-content-between
                               gap-2 mb-3">

                        <div>

                            <h5
                                class="fw-bold mb-1">

                                <i
                                    class="bi bi-map me-1">
                                </i>

                                Mission Map

                            </h5>


                            <p
                                class="small text-muted mb-0">

                                Your current position
                                and assigned incident location.

                            </p>

                        </div>


                        <span
                            class="badge
                                   rounded-pill
                                   bg-danger-subtle
                                   text-danger
                                   align-self-start">

                            Live Tracking

                        </span>

                    </div>


                    {{-- MAP --}}
                    <div class="map-shell">

                        <div id="map"></div>

                    </div>


                    {{-- =================================================
                         ACTIVE DISPATCH
                    ================================================== --}}

                    @if(
                    $currentDispatch
                    &&
                    $currentDispatch->incident
                    )


                    <div class="info-grid">


                        {{-- INCIDENT --}}
                        <div class="info-box">

                            <div class="info-label">
                                Incident
                            </div>

                            <div class="info-value">

                                {{
                                        $currentDispatch
                                        ->incident
                                        ->incident_number
                                    }}

                            </div>

                        </div>


                        {{-- LOCATION --}}
                        <div class="info-box">

                            <div class="info-label">
                                Location
                            </div>

                            <div class="info-value">

                                {{
                                        $currentDispatch
                                        ->incident
                                        ->location
                                    }}
                                <div class="small text-muted">{{ collect([$currentDispatch->incident->house_number, $currentDispatch->incident->street, $currentDispatch->incident->barangay, $currentDispatch->incident->city, $currentDispatch->incident->province])->filter()->implode(', ') }}</div>

                            </div>

                        </div>

                        <div class="info-box">
                            <div class="info-label">Classification</div>
                            <div class="info-value">{{ $currentDispatch->incident->incident_type }}</div>
                        </div>


                        {{-- VEHICLE --}}
                        <div class="info-box">

                            <div class="info-label">
                                Vehicle
                            </div>

                            <div class="info-value">

                                {{ $vehicleName }}

                                @if($vehiclePlate)

                                <span
                                    class="text-muted">

                                    • {{ $vehiclePlate }}

                                </span>

                                @endif

                            </div>

                        </div>


                        {{-- STATUS --}}
                        <div class="info-box">

                            <div class="info-label">
                                Dispatch Status
                            </div>

                            <div class="info-value">

                                @php

                                $dispatchBadge =
                                match($dispatchStatus) {

                                \App\Models\Dispatch::STATUS_PENDING,
                                \App\Models\Dispatch::STATUS_ASSIGNED
                                => 'bg-primary',

                                \App\Models\Dispatch::STATUS_ACCEPTED
                                => 'bg-success',

                                \App\Models\Dispatch::STATUS_EN_ROUTE
                                => 'bg-warning text-dark',

                                \App\Models\Dispatch::STATUS_ARRIVED
                                => 'bg-info text-dark',

                                default
                                => 'bg-secondary',
                                };

                                @endphp


                                <span
                                    class="badge
                                               {{ $dispatchBadge }}">

                                    {{
                                            strtoupper(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $dispatchStatus
                                                    ?? 'unknown'
                                                )
                                            )
                                        }}

                                </span>

                            </div>

                        </div>

                        <div class="info-box">
                            <div class="info-label">Event Times</div>
                            <div class="info-value small">
                                Created: {{ $currentDispatch->created_at?->format('M d, Y h:i A') ?? 'N/A' }}<br>
                                Accepted: {{ $currentDispatch->accepted_at?->format('M d, Y h:i A') ?? 'Pending' }}<br>
                                En route: {{ $currentDispatch->en_route_at?->format('M d, Y h:i A') ?? 'Pending' }}<br>
                                Arrived: {{ $currentDispatch->arrived_at?->format('M d, Y h:i A') ?? 'Pending' }}<br>
                                Completed: {{ $currentDispatch->completed_at?->format('M d, Y h:i A') ?? 'Pending' }}
                            </div>
                        </div>

                    </div>


                    {{-- =================================================
                             ACTIONS
                        ================================================== --}}

                    <div
                        class="dispatch-actions mt-3">


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

                        <form
                            method="POST"
                            action="{{ route(
                                        'driver.dispatch.accept',
                                        $currentDispatch
                                    ) }}"
                            class="flex-fill">

                            @csrf

                            <button
                                type="submit"
                                class="btn btn-primary
                                               w-100
                                               driver-action-btn">

                                <i
                                    class="bi bi-check-circle me-1">
                                </i>

                                Accept Dispatch

                            </button>

                        </form>


                        <form
                            method="POST"
                            action="{{ route(
                                        'driver.dispatch.decline',
                                        $currentDispatch
                                    ) }}"
                            class="flex-fill">

                            @csrf

                            <button
                                type="submit"
                                class="btn btn-outline-danger
                                               w-100
                                               driver-action-btn">

                                <i
                                    class="bi bi-x-circle me-1">
                                </i>

                                Decline Dispatch

                            </button>

                        </form>


                        {{-- ACCEPTED --}}
                        @elseif(
                        $currentDispatch->status
                        ===
                        \App\Models\Dispatch::STATUS_ACCEPTED
                        )

                        @if($currentDispatch->incident?->response_at === null)
                        <form
                            method="POST"
                            action="{{ route('driver.incidents.response', $currentDispatch->incident) }}"
                            class="flex-fill">

                            @csrf

                            <button
                                type="submit"
                                class="btn btn-primary
                                               w-100
                                               driver-action-btn">

                                <i
                                    class="bi bi-check2-circle me-1">
                                </i>

                                Mark Response

                            </button>

                        </form>
                        @else
                        <form method="POST" action="{{ route('driver.incidents.en-route', $currentDispatch->incident) }}" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-info w-100 driver-action-btn">
                                <i class="bi bi-sign-turn-right-fill me-1"></i>
                                Mark En Route
                            </button>
                        </form>
                        @endif


                        {{-- EN ROUTE --}}
                        @elseif(
                        $currentDispatch->status
                        ===
                        \App\Models\Dispatch::STATUS_EN_ROUTE
                        )

                        @if($currentDispatch->incident?->at_scene_at === null)
                        <form method="POST" action="{{ route('driver.incidents.at-scene', $currentDispatch->incident) }}" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100 driver-action-btn">
                                <i class="bi bi-geo-alt-fill me-1"></i>
                                Mark At Scene
                            </button>
                        </form>
                        @elseif($currentDispatch->incident?->at_patient_at === null)
                        <form method="POST" action="{{ route('driver.incidents.at-patient', $currentDispatch->incident) }}" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 driver-action-btn">
                                <i class="bi bi-person-heart me-1"></i>
                                Mark At Patient
                            </button>
                        </form>
                        @elseif($currentDispatch->incident?->depart_scene_at === null)
                        <form method="POST" action="{{ route('driver.incidents.depart-scene', $currentDispatch->incident) }}" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-info w-100 driver-action-btn">
                                <i class="bi bi-truck-front me-1"></i>
                                Depart Scene
                            </button>
                        </form>
                        @elseif($currentDispatch->incident?->at_hospital_at === null)
                        <form method="POST" action="{{ route('driver.incidents.at-hospital', $currentDispatch->incident) }}" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 driver-action-btn">
                                <i class="bi bi-hospital me-1"></i>
                                Mark At Hospital
                            </button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('driver.incidents.completed', $currentDispatch->incident) }}" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 driver-action-btn">
                                <i class="bi bi-check2-all me-1"></i>
                                Complete Incident
                            </button>
                        </form>
                        @endif


                        {{-- ARRIVED --}}
                        @elseif(
                        $currentDispatch->status
                        ===
                        \App\Models\Dispatch::STATUS_ARRIVED
                        )

                        @if($currentDispatch->incident?->at_patient_at === null)
                        <form method="POST" action="{{ route('driver.incidents.at-patient', $currentDispatch->incident) }}" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 driver-action-btn">
                                <i class="bi bi-person-heart me-1"></i>
                                Mark At Patient
                            </button>
                        </form>
                        @elseif($currentDispatch->incident?->depart_scene_at === null)
                        <form method="POST" action="{{ route('driver.incidents.depart-scene', $currentDispatch->incident) }}" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-info w-100 driver-action-btn">
                                <i class="bi bi-truck-front me-1"></i>
                                Depart Scene
                            </button>
                        </form>
                        @elseif($currentDispatch->incident?->at_hospital_at === null)
                        <form method="POST" action="{{ route('driver.incidents.at-hospital', $currentDispatch->incident) }}" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 driver-action-btn">
                                <i class="bi bi-hospital me-1"></i>
                                Mark At Hospital
                            </button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('driver.incidents.completed', $currentDispatch->incident) }}" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 driver-action-btn">
                                <i class="bi bi-check2-all me-1"></i>
                                Complete Incident
                            </button>
                        </form>
                        @endif

                        @endif


                        {{-- ASSIGNMENT --}}
                        <a
                            href="{{ route(
                                    'driver.assignment'
                                ) }}"
                            class="btn btn-outline-primary
                                       driver-action-btn
                                       flex-fill">

                            <i
                                class="bi bi-list-task me-1">
                            </i>

                            View Assignment

                        </a>

                    </div>


                    {{-- =================================================
                         REPORTABLE DISPATCH
                    ================================================== --}}

                    @elseif(
                    isset($reportableDispatch)
                    &&
                    $reportableDispatch?->incident
                    )

                    <div class="info-grid mt-3">

                        <div class="info-box">

                            <div class="info-label">
                                Reportable Incident
                            </div>

                            <div class="info-value">

                                {{
                                        $reportableDispatch
                                        ->incident
                                        ->incident_number
                                    }}

                            </div>

                        </div>


                        <div class="info-box">

                            <div class="info-label">
                                Location
                            </div>

                            <div class="info-value">

                                {{
                                        $reportableDispatch
                                        ->incident
                                        ->location
                                    }}

                            </div>

                        </div>

                    </div>


                    <div class="mt-3">

                        <a
                            href="{{ route(
                                    'driver.report.create',
                                    $reportableDispatch->incident
                                ) }}"
                            class="btn btn-primary
                                       driver-action-btn">

                            <i
                                class="bi bi-file-earmark-text me-1">
                            </i>

                            Submit Report

                        </a>

                    </div>


                    {{-- =================================================
                         NO DISPATCH
                    ================================================== --}}

                    @else

                    <div class="empty-state">

                        <div class="empty-icon">

                            <i class="bi bi-inbox"></i>

                        </div>


                        <h6 class="fw-bold mb-1">

                            No Active Dispatch

                        </h6>


                        <p
                            class="small text-muted mb-0">

                            Your dashboard will update
                            when a dispatch is assigned.

                        </p>

                    </div>

                    @endif

                </div>

            </div>


            {{-- =================================================
                 ASSIGNED INCIDENTS
            ================================================== --}}

            <div
                class="card
                       rounded-4
                       shadow-sm
                       mt-4">

                <div class="card-body">

                    <div class="mb-3">

                        <h5
                            class="fw-bold mb-1">

                            Assigned Incidents

                        </h5>


                        <p
                            class="small text-muted mb-0">

                            Recent assignments and activity
                            for this driver.

                        </p>

                    </div>


                    <div class="table-responsive">

                        <table
                            class="table
                                   table-sm
                                   align-middle
                                   mb-0">

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

                                @forelse(
                                $incidents->take(5)
                                as $incident
                                )

                                <tr>

                                    <td>

                                        <div
                                            class="fw-semibold">

                                            {{
                                                    $incident
                                                    ->incident_number
                                                }}

                                        </div>


                                        <div
                                            class="small
                                                       text-muted">

                                            {{
                                                    $incident
                                                    ->location
                                                }}

                                        </div>

                                    </td>


                                    <td>

                                        @php

                                        $incidentBadge =
                                        match(
                                        $incident->status
                                        ) {

                                        'pending'
                                        => 'bg-secondary',

                                        'assigned'
                                        => 'bg-primary',

                                        'accepted'
                                        => 'bg-success',

                                        'en_route'
                                        => 'bg-warning text-dark',

                                        'arrived',
                                        'on_scene'
                                        => 'bg-info text-dark',

                                        'completed',
                                        'closed'
                                        => 'bg-dark',

                                        default
                                        => 'bg-secondary',
                                        };

                                        @endphp


                                        <span
                                            class="badge
                                                       {{ $incidentBadge }}">

                                            {{
                                                    strtoupper(
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            $incident->status
                                                        )
                                                    )
                                                }}

                                        </span>

                                    </td>

                                </tr>

                                @empty

                                <tr>

                                    <td
                                        colspan="2"
                                        class="text-center
                                                   text-muted
                                                   py-4">

                                        No recent activity

                                    </td>

                                </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
             RIGHT COLUMN
        ====================================================== --}}

        <div class="col-12 col-xl-4">


            {{-- QUICK ACTIONS --}}
            <div
                class="card
                       rounded-4
                       shadow-sm
                       mb-4">

                <div class="card-body">

                    <h5
                        class="fw-bold mb-3">

                        <i
                            class="bi bi-lightning-charge me-1">
                        </i>

                        Quick Actions

                    </h5>


                    <div class="d-grid gap-2">

                        <a
                            href="{{ route(
                                'driver.navigation'
                            ) }}"
                            class="btn btn-outline-primary
                                   driver-action-btn">

                            <i
                                class="bi bi-geo-alt me-1">
                            </i>

                            Open Navigation

                        </a>


                        <a
                            href="{{ route(
                                'driver.assignment'
                            ) }}"
                            class="btn btn-outline-primary
                                   driver-action-btn">

                            <i
                                class="bi bi-list-check me-1">
                            </i>

                            My Assignment

                        </a>


                        <a
                            href="{{ route(
                                'driver.history'
                            ) }}"
                            class="btn btn-outline-primary
                                   driver-action-btn">

                            <i
                                class="bi bi-clock-history me-1">
                            </i>

                            Dispatch History

                        </a>

                    </div>

                </div>

            </div>


            {{-- VEHICLE --}}
            <div
                class="card
                       rounded-4
                       shadow-sm">

                <div class="card-body">

                    <h5
                        class="fw-bold mb-3">

                        <i
                            class="bi bi-info-circle me-1">
                        </i>

                        Current Vehicle

                    </h5>


                    <div class="vehicle-display">

                        <div class="vehicle-icon">

                            <i class="bi bi-truck"></i>

                        </div>


                        <div>

                            <div class="fw-bold">

                                {{ $vehicleName }}

                            </div>


                            @if($vehiclePlate)

                            <div
                                class="small text-muted">

                                {{ $vehiclePlate }}

                            </div>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


{{-- =========================================================
     JAVASCRIPT
========================================================== --}}

@section('scripts')

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function() {


            /* =====================================================
               LARAVEL CONNECTIONS
            ====================================================== */

            const csrfToken =
                @json(csrf_token());


            const gpsUpdateUrl =
                @json(route('driver.gps.update'));


            const panicUrl =
                @json(route('driver.panic.trigger'));


            const hijackUrl =
                @json(route('driver.hijack.trigger'));


            /* =====================================================
               INCIDENT COORDINATES
            ====================================================== */

            const incidentLat =
                @json(
                    $hasIncidentCoordinates ?
                    (float) $incidentLat :
                    null
                );


            const incidentLng =
                @json(
                    $hasIncidentCoordinates ?
                    (float) $incidentLng :
                    null
                );


            /* =====================================================
               VARIABLES
            ====================================================== */

            let map = null;

            let driverMarker = null;

            let incidentMarker = null;

            let gpsWatchId = null;

            let lastGpsSent = 0;

            let lastPosition = null;

            let pageUnloading = false;

            let pageVisible = !document.hidden;


            const GPS_INTERVAL =
                15000;


            /* =====================================================
               GPS STATUS
            ====================================================== */

            function setGpsStatus(
                type,
                text
            ) {

                const element =
                    document.getElementById(
                        'gpsStatus'
                    );


                if (!element) {
                    return;
                }


                element.textContent =
                    text;


                element.className =
                    'gps-status status-' +
                    type;

            }


            /* =====================================================
               MAP
            ====================================================== */

            function initMap() {

                const mapElement =
                    document.getElementById(
                        'map'
                    );


                if (!mapElement) {
                    return;
                }

                if (map) {
                    map.invalidateSize();
                    return;
                }


                if (typeof L === 'undefined') {

                    console.error(
                        'Leaflet is not loaded.'
                    );

                    return;
                }


                map =
                    L.map(
                        mapElement
                    ).setView(
                        [
                            15.9800,
                            120.5700
                        ],
                        13
                    );


                L.tileLayer(
                    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,

                        attribution: '&copy; OpenStreetMap contributors'
                    }
                ).addTo(map);


                /* INCIDENT MARKER */

                if (
                    incidentLat !== null &&
                    incidentLng !== null
                ) {

                    incidentMarker =
                        L.marker(
                            [
                                Number(incidentLat),
                                Number(incidentLng)
                            ], {
                                title: 'Incident Location'
                            }
                        )
                        .addTo(map)
                        .bindPopup(
                            '<strong>Incident Location</strong>'
                        );

                }


                setTimeout(
                    function() {

                        if (map) {

                            map.invalidateSize();

                        }

                    },
                    300
                );

            }


            /* =====================================================
               DRIVER MARKER
            ====================================================== */

            function updateDriverMarker(
                latitude,
                longitude
            ) {

                if (!map) {
                    return;
                }


                const position = [
                    Number(latitude),
                    Number(longitude)
                ];


                if (!driverMarker) {

                    driverMarker =
                        L.marker(
                            position, {
                                title: 'Your Current Position'
                            }
                        )
                        .addTo(map)
                        .bindPopup(
                            '<strong>Your Current Position</strong>'
                        );

                } else {

                    driverMarker.setLatLng(
                        position
                    );

                }


                /* FIT MAP */

                if (incidentMarker) {

                    const group =
                        L.featureGroup([
                            driverMarker,
                            incidentMarker
                        ]);


                    map.fitBounds(
                        group.getBounds(), {
                            padding: [
                                40,
                                40
                            ],

                            maxZoom: 16
                        }
                    );

                } else {

                    map.setView(
                        position,
                        15
                    );

                }

            }


            /* =====================================================
               SEND GPS TO LARAVEL
            ====================================================== */

            async function sendLocation(
                position
            ) {

                if (
                    !position ||
                    !pageVisible
                ) {

                    return;

                }


                const now =
                    Date.now();


                /*
                 * Prevent sending more than
                 * once every 15 seconds.
                 */

                if (
                    now - lastGpsSent <
                    GPS_INTERVAL
                ) {

                    updateDriverMarker(
                        position.coords.latitude,
                        position.coords.longitude
                    );

                    return;

                }


                lastGpsSent =
                    now;


                const latitude =
                    position.coords.latitude;


                const longitude =
                    position.coords.longitude;


                const accuracy =
                    position.coords.accuracy ??
                    null;

                const speedKmh = Number.isFinite(position.coords.speed) && position.coords.speed >= 0 ?
                    position.coords.speed * 3.6 :
                    null;

                const speedElement = document.getElementById('currentSpeed');
                if (speedElement) speedElement.textContent = speedKmh === null ? 'Speed: unavailable | Speed limit unavailable | UNRATED' : `Speed: ${speedKmh.toFixed(1)} km/h | Speed limit unavailable | UNRATED`;

                lastPosition = position;


                /*
                 * Update map immediately.
                 */

                updateDriverMarker(
                    latitude,
                    longitude
                );


                try {

                    const response =
                        await fetch(
                            gpsUpdateUrl, {
                                method: 'POST',

                                credentials: 'same-origin',

                                headers: {

                                    'Content-Type': 'application/json',

                                    'X-CSRF-TOKEN': csrfToken,

                                    'Accept': 'application/json'

                                },

                                body: JSON.stringify({

                                    latitude: latitude,

                                    longitude: longitude,

                                    accuracy: accuracy,
                                    speed_kmh: speedKmh

                                })

                            }
                        );


                    let data = null;


                    try {

                        data =
                            await response.json();

                    } catch (_) {

                        /*
                         * Endpoint may return
                         * an empty response.
                         */

                    }


                    if (!response.ok) {

                        throw new Error(
                            data?.message ??
                            'GPS update failed. HTTP ' +
                            response.status
                        );

                    }


                    console.log(
                        'GPS updated:',
                        data
                    );


                    setGpsStatus(
                        'live',
                        '🟢 Live Tracking'
                    );


                } catch (error) {

                    console.error(
                        'GPS server error:',
                        error
                    );


                    setGpsStatus(
                        'offline',
                        '🔴 Server Update Failed'
                    );

                }

            }


            /* =====================================================
               START GPS
            ====================================================== */

            function startGPS() {

                if (
                    pageUnloading ||
                    gpsWatchId !== null
                ) {
                    return;
                }

                if (
                    !navigator.geolocation
                ) {

                    setGpsStatus(
                        'unavailable',
                        '⚪ GPS Not Supported'
                    );

                    return;

                }


                /*
                 * Remove previous watcher.
                 */

                if (
                    gpsWatchId !== null
                ) {

                    navigator.geolocation.clearWatch(
                        gpsWatchId
                    );

                    gpsWatchId = null;

                }


                setGpsStatus(
                    'waiting',
                    '🟠 Waiting for GPS...'
                );


                gpsWatchId =
                    navigator.geolocation.watchPosition(

                        function(position) {

                            lastPosition = position;

                            if (!pageVisible) {
                                return;
                            }


                            sendLocation(
                                position
                            );

                        },


                        function(error) {

                            console.error(
                                'GPS Error:',
                                error
                            );


                            switch (
                                error.code
                            ) {

                                case 1:

                                    setGpsStatus(
                                        'permission',
                                        '🟠 Permission Denied'
                                    );

                                    break;


                                case 2:

                                    setGpsStatus(
                                        'unavailable',
                                        '⚪ Position Unavailable'
                                    );

                                    break;


                                case 3:

                                    /*
                                     * Do not call this
                                     * "server offline".
                                     * Code 3 means the browser
                                     * could not get a GPS fix
                                     * before the timeout.
                                     */

                                    setGpsStatus(
                                        'waiting',
                                        '🟠 Waiting for GPS...'
                                    );

                                    break;


                                default:

                                    setGpsStatus(
                                        'offline',
                                        '🔴 GPS Error'
                                    );

                                    break;

                            }

                        },


                        {
                            enableHighAccuracy: true,

                            timeout: 20000,

                            maximumAge: 10000
                        }

                    );

            }


            /* =====================================================
               STOP GPS
            ====================================================== */

            function stopGPS() {

                if (
                    gpsWatchId !== null &&
                    navigator.geolocation
                ) {

                    navigator.geolocation.clearWatch(
                        gpsWatchId
                    );


                    gpsWatchId =
                        null;

                }

            }


            /* =====================================================
               PAGE VISIBILITY
            ====================================================== */

            document.addEventListener(
                'visibilitychange',
                function() {

                    pageVisible = !document.hidden;


                    if (pageVisible) {

                        startGPS();

                    } else {

                        stopGPS();

                    }

                }
            );


            window.addEventListener(
                'pagehide',
                function() {

                    pageUnloading = true;

                    stopGPS();

                }
            );


            /* =====================================================
               EMERGENCY
            ====================================================== */

            async function triggerEmergency(
                url,
                label
            ) {

                const confirmed =
                    window.confirm(
                        'Trigger ' +
                        label +
                        ' alert? This will notify dispatch immediately.'
                    );


                if (!confirmed) {
                    return;
                }

                let position = lastPosition;

                if (!position && navigator.geolocation) {
                    try {
                        position = await new Promise(function(resolve, reject) {
                            navigator.geolocation.getCurrentPosition(
                                resolve,
                                reject, {
                                    enableHighAccuracy: true,
                                    timeout: 10000,
                                    maximumAge: 10000
                                }
                            );
                        });
                    } catch (error) {
                        console.error(label + ' GPS error:', error);
                    }
                }

                if (!position) {
                    window.alert(
                        'Unable to send ' + label + ' alert without a GPS position.'
                    );
                    return;
                }


                try {

                    const response =
                        await fetch(
                            url, {
                                method: 'POST',

                                credentials: 'same-origin',

                                headers: {

                                    'Content-Type': 'application/json',

                                    'X-CSRF-TOKEN': csrfToken,

                                    'Accept': 'application/json'

                                },

                                body: JSON.stringify({
                                    latitude: position.coords.latitude,
                                    longitude: position.coords.longitude
                                })

                            }
                        );


                    let data = null;


                    try {

                        data =
                            await response.json();

                    } catch (_) {

                        /*
                         * Empty successful response
                         * is allowed.
                         */

                    }


                    if (!response.ok) {

                        throw new Error(
                            data?.message ??
                            'HTTP ' +
                            response.status
                        );

                    }


                    console.log(
                        label +
                        ' response:',
                        data
                    );


                    window.alert(
                        label +
                        ' alert sent successfully!'
                    );


                } catch (error) {

                    console.error(
                        label +
                        ' error:',
                        error
                    );


                    window.alert(
                        'Failed to send ' +
                        label +
                        ' alert. Please try again.'
                    );

                }

            }


            /* =====================================================
               PANIC
            ====================================================== */

            const panicBtn =
                document.getElementById(
                    'panicBtn'
                );


            if (panicBtn) {

                panicBtn.addEventListener(
                    'click',
                    function() {

                        triggerEmergency(
                            panicUrl,
                            'PANIC'
                        );

                    }
                );

            }


            /* =====================================================
               HIJACK
            ====================================================== */

            const hijackBtn =
                document.getElementById(
                    'hijackBtn'
                );


            if (hijackBtn) {

                hijackBtn.addEventListener(
                    'click',
                    function() {

                        triggerEmergency(
                            hijackUrl,
                            'HIJACK'
                        );

                    }
                );

            }


            /* =====================================================
               START
            ====================================================== */

            initMap();

            startGPS();

        });
</script>

@endsection


{{-- =========================================================
     DASHBOARD CSS
========================================================== --}}

@push('styles')

<style>
    .driver-dashboard-shell {
        width: 100%;
    }


    /* =====================================================
       HERO
    ====================================================== */

    .hero-panel {

        background:
            linear-gradient(90deg,
                #06243a 0%,
                #083b57 65%);

        color: #fff;

    }


    .hero-panel-body {

        padding: 2rem;

    }


    .hero-side-panel {

        padding: 2rem;

        background:
            rgba(255, 255, 255, .07);

    }


    .hero-summary-card {

        height: 100%;

        padding: 1.25rem;

        border:
            1px solid rgba(255, 255, 255, .14);

        border-radius: 1rem;

        background:
            rgba(255, 255, 255, .10);

    }


    .hero-eyebrow {

        font-size: .72rem;

        font-weight: 700;

        letter-spacing: .16em;

        opacity: .78;

        margin-bottom: .5rem;

    }


    .hero-title {

        margin: 0 0 .65rem;

        font-size:
            clamp(1.35rem,
                2.5vw,
                2rem);

        font-weight: 700;

    }


    .hero-copy {

        max-width: 760px;

        margin-bottom: 1.5rem;

        color:
            rgba(255, 255, 255, .82);

        line-height: 1.6;

    }


    .hero-icon {

        width: 48px;
        height: 48px;

        display: grid;

        place-items: center;

        border-radius: 50%;

        background:
            rgba(255, 255, 255, .18);

        font-size: 1.2rem;

    }


    /* =====================================================
       STAT CARDS
    ====================================================== */

    .stat-card {

        transition:
            transform .2s ease,
            box-shadow .2s ease;

    }


    .stat-card:hover {

        transform:
            translateY(-2px);

        box-shadow:
            0 10px 30px rgba(0, 0, 0, .18);

    }


    .stat-card .card-body {

        min-height: 100px;

        padding: 1.15rem;

    }


    .stat-icon {

        width: 44px;
        height: 44px;

        display: grid;

        place-items: center;

        border-radius: 50%;

        color: #fff;

        flex-shrink: 0;

    }


    .stat-value {

        font-size: 1rem;

        font-weight: 700;

        color: #eef4ff;

    }


    /* =====================================================
       GPS
    ====================================================== */

    .gps-status {

        display: inline-block;

        padding:
            .3rem .65rem;

        border-radius:
            .5rem;

        font-size:
            .78rem;

        font-weight: 700;

        background:
            rgba(255, 255, 255, .08);

    }


    .gps-status.status-live {

        color:
            #5ee7b7;

        background:
            rgba(32, 201, 151, .14);

    }


    .gps-status.status-waiting {

        color:
            #ffc107;

        background:
            rgba(255, 193, 7, .12);

    }


    .gps-status.status-permission {

        color:
            #ffb74d;

        background:
            rgba(255, 152, 0, .12);

    }


    .gps-status.status-unavailable,
    .gps-status.status-offline {

        color:
            #ff8a80;

        background:
            rgba(244, 67, 54, .12);

    }


    /* =====================================================
       MAP
    ====================================================== */

    .map-shell {

        min-height: 360px;

        overflow: hidden;

        border-radius: 1rem;

        border:
            1px solid rgba(255, 255, 255, .08);

    }


    #map {

        width: 100%;

        height: 360px;

    }


    /* =====================================================
       INFO
    ====================================================== */

    .info-grid {

        display: grid;

        grid-template-columns:
            repeat(2,
                minmax(0, 1fr));

        gap: .75rem;

    }


    .info-box {

        padding: .9rem;

        border-radius: .85rem;

        background:
            rgba(255, 255, 255, .045);

        border:
            1px solid rgba(255, 255, 255, .07);

    }


    .info-label {

        margin-bottom: .2rem;

        font-size: .68rem;

        font-weight: 700;

        letter-spacing: .08em;

        text-transform: uppercase;

        color:
            rgba(255, 255, 255, .55);

    }


    .info-value {

        font-weight: 600;

        color: #eef4ff;

        word-break: break-word;

    }


    /* =====================================================
       DISPATCH ACTIONS
    ====================================================== */

    .dispatch-actions {

        display: flex;

        flex-wrap: wrap;

        gap: .6rem;

    }


    /* =====================================================
       EMPTY STATE
    ====================================================== */

    .empty-state {

        margin-top: 1rem;

        padding: 2rem 1rem;

        text-align: center;

        border:
            1px dashed rgba(255, 255, 255, .12);

        border-radius: 1rem;

        background:
            rgba(255, 255, 255, .025);

    }


    .empty-icon {

        width: 52px;
        height: 52px;

        margin:
            0 auto .75rem;

        display: grid;

        place-items: center;

        border-radius: 50%;

        background:
            rgba(255, 255, 255, .07);

        font-size: 1.25rem;

    }


    /* =====================================================
       VEHICLE
    ====================================================== */

    .vehicle-display {

        display: flex;

        align-items: center;

        gap: 1rem;

        padding: 1rem;

        border-radius: 1rem;

        background:
            rgba(255, 255, 255, .045);

    }


    .vehicle-icon {

        width: 48px;
        height: 48px;

        display: grid;

        place-items: center;

        border-radius: 50%;

        background:
            rgba(13, 110, 253, .18);

        color:
            #7fb0ff;

        font-size: 1.2rem;

    }


    /* =====================================================
       MOBILE
    ====================================================== */

    @media (max-width: 767.98px) {

        .hero-panel-body,
        .hero-side-panel {

            padding: 1rem;

        }


        .info-grid {

            grid-template-columns: 1fr;

        }


        .dispatch-actions {

            flex-direction: column;

        }


        .dispatch-actions>* {

            width: 100%;

        }


        .map-shell {

            min-height: 300px;

        }


        #map {

            height: 300px;

        }

    }
</style>

@endpush