<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>MuniResQ Driver</title>

    {{-- =====================================================
         BOOTSTRAP
    ====================================================== --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    {{-- =====================================================
         BOOTSTRAP ICONS
    ====================================================== --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- =====================================================
         LEAFLET
    ====================================================== --}}
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        crossorigin="">

    {{-- =====================================================
         LEAFLET ROUTING MACHINE
    ====================================================== --}}
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css">

    {{-- =====================================================
         VITE
    ====================================================== --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])


    <style>

        /* =====================================================
           BODY
        ====================================================== */

        body {
            margin: 0;
            background: #071329;
            color: #eef4ff;
        }


        /* =====================================================
           APP SHELL
        ====================================================== */

        .app-shell {
            min-height: 100vh;
            width: 100%;
        }


        /* =====================================================
           SIDEBAR
        ====================================================== */

        .sidebar-driver {
            width: 280px;
            min-width: 280px;
            min-height: 100vh;

            background:
                linear-gradient(
                    180deg,
                    #06243a 0%,
                    #0b3658 100%
                );

            color: #f8fafc;
        }


        /* =====================================================
           BRAND
        ====================================================== */

        .brand {
            border-bottom:
                1px solid rgba(255, 255, 255, 0.08);
        }


        .brand-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;

            background:
                rgba(255, 255, 255, 0.16);

            display: flex;
            align-items: center;
            justify-content: center;
        }


        /* =====================================================
           DRIVER PROFILE
        ====================================================== */

        .driver-profile {
            border-bottom:
                1px solid rgba(255, 255, 255, 0.08);
        }


        .driver-avatar {
            position: relative;

            width: 70px;
            height: 70px;

            border-radius: 50%;

            background:
                rgba(255, 255, 255, 0.16);

            display: grid;
            place-items: center;

            font-size: 1.8rem;
        }


        .status-dot {
            position: absolute;

            bottom: 4px;
            right: 4px;

            width: 16px;
            height: 16px;

            border-radius: 50%;

            border:
                2px solid #06243a;
        }


        .status-dot.online {
            background: #20c997;
        }


        .status-dot.offline {
            background: #f4b942;
        }


        /* =====================================================
           SIDEBAR LINKS
        ====================================================== */

        .sidebar-link {
            display: flex;
            align-items: center;

            gap: 0.8rem;

            padding: 0.85rem 0.95rem;

            margin-bottom: 0.35rem;

            border-radius: 0.9rem;

            color:
                rgba(255, 255, 255, 0.88);

            text-decoration: none;

            transition:
                background-color .2s ease,
                color .2s ease;
        }


        .sidebar-link:hover,
        .sidebar-link.active {
            background-color:
                rgba(255, 255, 255, 0.12);

            color: #ffffff;
        }


        /* =====================================================
           CONTENT
        ====================================================== */

        .content-area {
            min-width: 0;
            min-height: 100vh;

            overflow-x: hidden;

            background:
                radial-gradient(
                    circle at top left,
                    rgba(59, 105, 255, 0.12),
                    transparent 28%
                ),
                linear-gradient(
                    180deg,
                    #071329 0%,
                    #08172f 100%
                );
        }


        /* =====================================================
           CARDS
        ====================================================== */

        .card {
            background:
                rgba(7, 18, 38, 0.92);

            border:
                1px solid rgba(255, 255, 255, 0.08);

            border-radius: 1.5rem;
        }


        .card-body,
        .card-header,
        .card-footer {
            color:
                rgba(255, 255, 255, 0.92);
        }


        .card-header {
            background:
                rgba(11, 26, 49, 0.92);

            border-bottom:
                1px solid rgba(255, 255, 255, 0.1);
        }


        .text-muted {
            color:
                rgba(255, 255, 255, 0.72) !important;
        }


        /* =====================================================
           TABLE
        ====================================================== */

        .table thead th,
        .table tbody td,
        .table tbody th {
            background:
                rgba(255, 255, 255, 0.04) !important;

            color:
                #eef4ff !important;

            border-color:
                rgba(255, 255, 255, 0.08) !important;
        }


        /* =====================================================
           FORM
        ====================================================== */

        .form-control,
        .form-select,
        .input-group-text,
        .form-check-input {
            background:
                rgba(255, 255, 255, 0.06);

            border-color:
                rgba(255, 255, 255, 0.14);

            color:
                #eef4ff;
        }


        .form-control:focus,
        .form-select:focus {
            background:
                rgba(255, 255, 255, 0.08);

            border-color:
                rgba(13, 110, 253, 0.9);

            color:
                #eef4ff;

            box-shadow:
                0 0 0 0.2rem rgba(13, 110, 253, 0.2);
        }


        .form-control::placeholder {
            color:
                rgba(255, 255, 255, 0.65);
        }


        label,
        legend,
        .form-check-label {
            color:
                rgba(255, 255, 255, 0.88);
        }


        /* =====================================================
           MOBILE HEADER
        ====================================================== */

        .driver-mobile-header {
            min-height: 52px;
            padding: 0.35rem 0;
        }


        .mobile-nav-toggle {
            min-height: 46px;
            border-radius: 999px;
        }


        /* =====================================================
           MOBILE OFFCANVAS
        ====================================================== */

        .driver-offcanvas {
            width: 280px !important;

            background:
                linear-gradient(
                    180deg,
                    #06243a 0%,
                    #0b3658 100%
                ) !important;

            color:
                #ffffff !important;
        }


        .driver-offcanvas .offcanvas-header {
            background:
                #06243a !important;

            color:
                #ffffff !important;

            border-bottom:
                1px solid rgba(255, 255, 255, 0.08) !important;
        }


        .driver-offcanvas .offcanvas-body {
            background:
                #06243a !important;

            color:
                #ffffff !important;
        }


        .driver-mobile-profile {
            background:
                #06243a !important;

            color:
                #ffffff !important;

            border-bottom:
                1px solid rgba(255, 255, 255, 0.08);
        }


        .driver-offcanvas .sidebar-link {
            color:
                rgba(255, 255, 255, 0.88) !important;
        }


        .driver-offcanvas .sidebar-link:hover,
        .driver-offcanvas .sidebar-link.active {
            background:
                rgba(255, 255, 255, 0.12) !important;

            color:
                #ffffff !important;
        }


        /* =====================================================
           DASHBOARD
        ====================================================== */

        .driver-dashboard-shell {
            display: block;
        }


        /* =====================================================
           HERO
        ====================================================== */

        .hero-panel {
            background:
                linear-gradient(
                    90deg,
                    #06243a 0%,
                    #083b57 60%
                );

            color:
                #f6f8fb;
        }


        .hero-panel-body {
            padding: 1.5rem;
        }


        .hero-side-panel {
            padding: 1.5rem;

            background:
                rgba(255, 255, 255, .06);
        }


        .hero-eyebrow {
            letter-spacing: .16em;
            opacity: .78;
        }


        .hero-copy {
            color:
                rgba(255, 255, 255, .82);

            line-height: 1.7;
        }


        .hero-summary-card {
            background:
                rgba(255, 255, 255, .14);

            border:
                1px solid rgba(255, 255, 255, .16);

            border-radius: 1rem;

            padding: 1rem;

            height: 100%;
        }


        .hero-icon {
            width: 48px;
            height: 48px;

            border-radius: 50%;

            display: grid;
            place-items: center;

            background:
                rgba(255, 255, 255, .18);

            font-size: 1.2rem;
        }


        .driver-action-btn {
            min-height: 46px;

            border-radius: 999px;

            padding-inline: 1rem;
        }


        /* =====================================================
           STAT CARDS
        ====================================================== */

        .stat-card {
            border:
                1px solid rgba(0, 0, 0, .04);
        }


        .stat-icon {
            width: 46px;
            height: 46px;

            border-radius: 50%;

            display: grid;
            place-items: center;

            font-size: 1.1rem;
        }


        /* =====================================================
           MAP
        ====================================================== */

        .map-shell {
            min-height: 360px;

            border-radius: 1rem;

            overflow: hidden;

            border:
                1px solid rgba(0, 0, 0, .06);

            background: #d9e2ec;
        }


        #map {
            width: 100%;
            height: 360px;
        }


        /* =====================================================
           LEAFLET FIX
        ====================================================== */

        .leaflet-container {
            width: 100%;
            height: 100%;

            font-family:
                inherit;
        }


        /* =====================================================
           TABLE
        ====================================================== */

        .table-responsive {
            overflow-x: auto;
        }


        /* =====================================================
           MOBILE
        ====================================================== */

        @media (max-width: 575.98px) {

            .content-area .container-fluid {
                padding: 0.75rem !important;
            }


            .mobile-nav-toggle {
                min-height: 48px;
            }

        }


        /* =====================================================
           TABLET
        ====================================================== */

        @media (min-width: 768px) {

            .hero-panel-body {
                padding: 2rem;
            }


            .hero-side-panel {
                padding: 2rem;
            }


            #map {
                height: 430px;
            }


            .map-shell {
                min-height: 430px;
            }

        }


        /* =====================================================
           DESKTOP
        ====================================================== */

        @media (min-width: 992px) {

            .sidebar-driver {
                display: flex !important;
            }


            .mobile-nav-toggle {
                display: none;
            }

        }


        @media (min-width: 1024px) {

            .hero-panel-body {
                padding: 2.25rem;
            }


            .hero-side-panel {
                padding: 2.25rem;
            }


            #map {
                height: 480px;
            }


            .map-shell {
                min-height: 480px;
            }

        }

    </style>

</head>


<body class="driver-layout">


<div class="app-shell d-flex flex-column flex-lg-row">


    {{-- =====================================================
         DESKTOP SIDEBAR
    ====================================================== --}}

    <aside class="sidebar-driver d-none d-lg-flex flex-column">


        {{-- BRAND --}}

        <div class="brand p-3 d-flex align-items-center gap-2">

            <div class="brand-icon">

                <i class="bi bi-hospital text-white fs-4"></i>

            </div>

            <div>

                <div class="h6 mb-0 text-white">
                    MuniResQ
                </div>

                <small class="text-white-50">
                    Driver Operations
                </small>

            </div>

        </div>


        {{-- DRIVER PROFILE --}}

        <div class="driver-profile p-3 text-center">

            <div class="driver-avatar mx-auto mb-2">

                <i class="bi bi-person-fill"></i>

                <span
                    class="status-dot online"
                    title="Available">
                </span>

            </div>


            <div class="fw-semibold text-white">
                {{ auth()->user()->name }}
            </div>


            <div class="text-white-50 small">

                @if(auth()->user()->driver)

                    <span class="me-2">
                        Badge:
                        {{ auth()->user()->driver->badge_id }}
                    </span>

                @endif

                <span class="badge bg-light text-dark">
                    Available
                </span>

            </div>

        </div>


        {{-- NAVIGATION --}}

        <nav class="nav flex-column sidebar-nav p-3">

            <a
                href="{{ url('/driver/dashboard') }}"
                class="sidebar-link active">

                <i class="bi bi-speedometer2 fs-4"></i>

                <span>
                    Dashboard
                </span>

            </a>


            <a
                href="{{ url('/driver/navigation') }}"
                class="sidebar-link">

                <i class="bi bi-geo-alt-fill fs-4"></i>

                <span>
                    Navigation
                </span>

            </a>


            <a
                href="{{ url('/driver/my-assignment') }}"
                class="sidebar-link">

                <i class="bi bi-list-check fs-4"></i>

                <span>
                    My Assignment
                </span>

            </a>


            <a
                href="{{ url('/driver/incidents/report') }}"
                class="sidebar-link">

                <i class="bi bi-file-earmark-medical fs-4"></i>

                <span>
                    Reports
                </span>

            </a>


            <a
                href="{{ url('/driver/history') }}"
                class="sidebar-link">

                <i class="bi bi-clock-history fs-4"></i>

                <span>
                    Dispatch History
                </span>

            </a>


            <a
                href="{{ url('/driver/settings') }}"
                class="sidebar-link">

                <i class="bi bi-gear fs-4"></i>

                <span>
                    Settings
                </span>

            </a>


            <a
                href="{{ url('/profile') }}"
                class="sidebar-link">

                <i class="bi bi-person-circle fs-4"></i>

                <span>
                    Profile
                </span>

            </a>

        </nav>


        {{-- LOGOUT --}}

        <div class="mt-auto p-3">

            <form
                method="POST"
                action="{{ url('/logout') }}">

                @csrf

                <button
                    type="submit"
                    class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2 rounded-pill">

                    <i class="bi bi-box-arrow-right"></i>

                    <span>
                        Logout
                    </span>

                </button>

            </form>

        </div>

    </aside>



    {{-- =====================================================
         MOBILE OFFCANVAS
    ====================================================== --}}

    <div
        class="offcanvas offcanvas-start d-lg-none driver-offcanvas"
        tabindex="-1"
        id="driverOffcanvas"
        aria-labelledby="driverOffcanvasLabel">


        <div class="offcanvas-header">

            <h5
                class="offcanvas-title"
                id="driverOffcanvasLabel">

                Driver Navigation

            </h5>


            <button
                type="button"
                class="btn-close btn-close-white"
                data-bs-dismiss="offcanvas"
                aria-label="Close">
            </button>

        </div>


        <div class="offcanvas-body p-0">


            <div class="p-3 driver-mobile-profile">

                <div class="fw-semibold">

                    {{ auth()->user()->name }}

                </div>


                <div class="small text-white-50">

                    Available

                </div>

            </div>


            <nav class="nav flex-column p-3">


                <a
                    href="{{ url('/driver/dashboard') }}"
                    class="sidebar-link active"
                    data-bs-dismiss="offcanvas">

                    <i class="bi bi-speedometer2 fs-4"></i>

                    <span>
                        Dashboard
                    </span>

                </a>


                <a
                    href="{{ url('/driver/navigation') }}"
                    class="sidebar-link">

                    <i class="bi bi-geo-alt-fill fs-4"></i>

                    <span>
                        Navigation
                    </span>

                </a>


                <a
                    href="{{ url('/driver/my-assignment') }}"
                    class="sidebar-link">

                    <i class="bi bi-list-check fs-4"></i>

                    <span>
                        My Assignment
                    </span>

                </a>


                <a
                    href="{{ url('/driver/incidents/report') }}"
                    class="sidebar-link">

                    <i class="bi bi-file-earmark-medical fs-4"></i>

                    <span>
                        Reports
                    </span>

                </a>


                <a
                    href="{{ url('/driver/history') }}"
                    class="sidebar-link">

                    <i class="bi bi-clock-history fs-4"></i>

                    <span>
                        Dispatch History
                    </span>

                </a>


                <a
                    href="{{ url('/driver/settings') }}"
                    class="sidebar-link">

                    <i class="bi bi-gear fs-4"></i>

                    <span>
                        Settings
                    </span>

                </a>


                <a
                    href="{{ url('/profile') }}"
                    class="sidebar-link">

                    <i class="bi bi-person-circle fs-4"></i>

                    <span>
                        Profile
                    </span>

                </a>

            </nav>

        </div>

    </div>



    {{-- =====================================================
         MAIN CONTENT
    ====================================================== --}}

    <main class="content-area flex-grow-1">


        <div class="container-fluid p-3 p-lg-4">


            {{-- MOBILE HEADER --}}

            <div
                class="driver-mobile-header d-flex justify-content-between align-items-center mb-3 d-lg-none">


                <button
                    class="btn btn-outline-primary mobile-nav-toggle"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#driverOffcanvas"
                    aria-controls="driverOffcanvas">

                    <i class="bi bi-list me-2"></i>

                    Menu

                </button>


                <div class="text-end">

                    <div class="fw-semibold">
                        MuniResQ
                    </div>

                    <div class="small text-muted">
                        Driver Operations
                    </div>

                    <div class="small text-muted">

                        {{ auth()->user()->name }}

                        ·

                        Available

                    </div>

                </div>

            </div>



            {{-- =====================================================
                 DASHBOARD
            ====================================================== --}}

            <div class="driver-dashboard-shell">


                {{-- =================================================
                     HERO
                ================================================== --}}

                <div
                    class="card border-0 shadow-sm rounded-4 hero-panel overflow-hidden mb-4">


                    <div class="row g-0 align-items-stretch">


                        <div
                            class="col-12 col-lg-8 hero-panel-body">


                            <div
                                class="small text-uppercase fw-semibold hero-eyebrow">

                                Emergency Operations

                            </div>


                            <h2 class="fw-bold mb-2">

                                Driver Operations Center

                            </h2>


                            <p class="mb-3 mb-lg-4 hero-copy">

                                {{ auth()->user()->name }}

                                —

                                monitor active dispatches and coordinate ambulance response with precision.

                            </p>


                            <div class="d-flex flex-wrap gap-2">


                                {{-- PANIC --}}

                                <button
                                    id="panicBtn"
                                    type="button"
                                    class="btn btn-danger btn-lg driver-action-btn">

                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>

                                    Panic

                                </button>


                                {{-- HIJACK --}}

                                <button
                                    id="hijackBtn"
                                    type="button"
                                    class="btn btn-warning btn-lg driver-action-btn">

                                    <i class="bi bi-shield-exclamation me-1"></i>

                                    Hijack

                                </button>


                                {{-- REPORT --}}

                                <a
                                    href="{{ url('/driver/incidents/report') }}"
                                    class="btn btn-outline-light btn-lg driver-action-btn">

                                    <i class="bi bi-file-earmark-medical me-1"></i>

                                    Report

                                </a>


                                {{-- LOGOUT --}}

                                <form
                                    method="POST"
                                    action="{{ url('/logout') }}"
                                    class="d-inline">

                                    @csrf

                                    <button
                                        type="submit"
                                        class="btn btn-outline-light btn-lg driver-action-btn">

                                        <i class="bi bi-box-arrow-right me-1"></i>

                                        Logout

                                    </button>

                                </form>


                            </div>


                        </div>


                        {{-- HERO SIDE --}}

                        <div
                            class="col-12 col-lg-4 hero-side-panel">


                            <div class="hero-summary-card">


                                <div
                                    class="d-flex align-items-center gap-3 mb-3">


                                    <div class="hero-icon">

                                        <i class="bi bi-truck-flatbed"></i>

                                    </div>


                                    <div>

                                        <div class="small text-uppercase opacity-75">

                                            Driver Status

                                        </div>


                                        <span
                                            class="badge bg-success fs-6 px-3 py-2">

                                            AVAILABLE

                                        </span>

                                    </div>


                                </div>


                                <div>

                                    <div class="small text-uppercase opacity-75">

                                        Active Dispatch

                                    </div>


                                    <div class="fw-semibold">

                                        None

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

                        <div
                            class="card border-0 shadow-sm rounded-4 stat-card h-100">

                            <div
                                class="card-body d-flex align-items-center gap-3">

                                <div
                                    class="stat-icon bg-primary text-white">

                                    <i class="bi bi-activity"></i>

                                </div>

                                <div>

                                    <div class="small text-muted">
                                        Active Dispatch
                                    </div>

                                    <div class="fw-bold">
                                        —
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- VEHICLE --}}

                    <div class="col-12 col-sm-6 col-lg-3">

                        <div
                            class="card border-0 shadow-sm rounded-4 stat-card h-100">

                            <div
                                class="card-body d-flex align-items-center gap-3">

                                <div
                                    class="stat-icon bg-info text-white">

                                    <i class="bi bi-truck"></i>

                                </div>

                                <div>

                                    <div class="small text-muted">
                                        Vehicle
                                    </div>


                                    @if(
                                        auth()->user()->driver &&
                                        auth()->user()->driver->vehicle
                                    )

                                        <div class="fw-bold">

                                            {{ auth()->user()->driver->vehicle->make ?? 'Vehicle' }}

                                        </div>

                                        <div class="small text-muted">

                                            {{ auth()->user()->driver->vehicle->plate_number ?? '' }}

                                        </div>

                                    @else

                                        <div class="fw-bold">
                                            No Vehicle
                                        </div>

                                    @endif

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- DRIVER STATUS --}}

                    <div class="col-12 col-sm-6 col-lg-3">

                        <div
                            class="card border-0 shadow-sm rounded-4 stat-card h-100">

                            <div
                                class="card-body d-flex align-items-center gap-3">

                                <div
                                    class="stat-icon bg-success text-white">

                                    <i class="bi bi-person-badge"></i>

                                </div>

                                <div>

                                    <div class="small text-muted">
                                        Driver Status
                                    </div>

                                    <span
                                        class="badge bg-success fs-6 px-3 py-2">

                                        AVAILABLE

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- GPS --}}

                    <div class="col-12 col-sm-6 col-lg-3">

                        <div
                            class="card border-0 shadow-sm rounded-4 stat-card h-100">

                            <div
                                class="card-body d-flex align-items-center gap-3">

                                <div
                                    class="stat-icon bg-secondary text-white">

                                    <i class="bi bi-geo-alt"></i>

                                </div>

                                <div>

                                    <div class="small text-muted">
                                        GPS Status
                                    </div>

                                    <div
                                        id="gpsStatus"
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
                     MAP + INCIDENTS
                ================================================== --}}

                <div class="row g-4">


                    <div class="col-12 col-xl-8">


                        {{-- MAP CARD --}}

                        <div
                            class="card border-0 shadow-sm rounded-4">


                            <div class="card-body">


                                <div
                                    class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">


                                    <div>

                                        <h5 class="fw-bold mb-1">
                                            Mission Map
                                        </h5>

                                        <p
                                            class="text-muted small mb-0">

                                            Live position and incident location for rapid response.

                                        </p>

                                    </div>


                                    <span
                                        class="badge rounded-pill bg-danger-subtle text-danger align-self-start">

                                        <i class="bi bi-broadcast-pin me-1"></i>

                                        Live Tracking

                                    </span>


                                </div>


                                {{-- MAP --}}

                                <div class="map-shell">

                                    <div id="map"></div>

                                </div>


                                {{-- VEHICLE --}}

                                <div
                                    class="mt-3 mb-3 p-3 rounded-3 bg-secondary bg-opacity-10">


                                    <div
                                        class="small text-muted text-uppercase">

                                        Vehicle

                                    </div>


                                    @if(
                                        auth()->user()->driver &&
                                        auth()->user()->driver->vehicle
                                    )

                                        <div class="fw-semibold">

                                            {{ auth()->user()->driver->vehicle->make ?? 'Vehicle' }}

                                            •

                                            {{ auth()->user()->driver->vehicle->plate_number ?? 'No Plate' }}

                                        </div>

                                    @else

                                        <div class="fw-semibold">

                                            No Vehicle Assigned

                                        </div>

                                    @endif

                                </div>


                                {{-- STATUS --}}

                                <div class="mb-2">

                                    <div
                                        class="small text-muted text-uppercase">

                                        Status

                                    </div>

                                    <div class="mt-1">

                                        <span class="badge bg-secondary">

                                            No Active Dispatch

                                        </span>

                                    </div>

                                </div>


                            </div>

                        </div>



                        {{-- =================================================
                             ASSIGNED INCIDENTS
                        ================================================== --}}

                        <div
                            class="card border-0 shadow-sm rounded-4 mt-4">


                            <div class="card-body">


                                <h6 class="fw-bold mb-2">
                                    Assigned Incidents
                                </h6>


                                <p
                                    class="small text-muted mb-3">

                                    Recent assignments and activity for this driver.

                                </p>


                                <div class="table-responsive">


                                    <table
                                        class="table table-sm align-middle mb-0">


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
                                                $incidents ?? []
                                                as $incident
                                            )

                                                <tr>

                                                    <td>

                                                        <div
                                                            class="fw-semibold">

                                                            {{ $incident->incident_number ?? 'INC-' . $incident->id }}

                                                        </div>


                                                        <div
                                                            class="small text-muted">

                                                            {{ $incident->location ?? 'Unknown location' }}

                                                        </div>

                                                    </td>


                                                    <td>

                                                        @php

                                                            $status = strtolower(
                                                                $incident->status ?? 'unknown'
                                                            );

                                                        @endphp


                                                        @if($status === 'closed')

                                                            <span
                                                                class="badge bg-secondary text-white">

                                                                Closed

                                                            </span>

                                                        @elseif($status === 'active')

                                                            <span
                                                                class="badge bg-danger">

                                                                Active

                                                            </span>

                                                        @elseif($status === 'dispatched')

                                                            <span
                                                                class="badge bg-warning text-dark">

                                                                Dispatched

                                                            </span>

                                                        @else

                                                            <span
                                                                class="badge bg-secondary">

                                                                {{ ucfirst($status) }}

                                                            </span>

                                                        @endif

                                                    </td>

                                                </tr>


                                            @empty

                                                <tr>

                                                    <td
                                                        colspan="2"
                                                        class="text-center text-muted py-4">

                                                        No assigned incidents.

                                                    </td>

                                                </tr>

                                            @endforelse


                                        </tbody>

                                    </table>

                                </div>


                            </div>

                        </div>


                    </div>


                </div>


            </div>


        </div>


    </main>


</div>



{{-- =====================================================
     BOOTSTRAP JS
====================================================== --}}

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>



{{-- =====================================================
     LEAFLET JS
====================================================== --}}

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    crossorigin="">
</script>



{{-- =====================================================
     LEAFLET ROUTING MACHINE
====================================================== --}}

<script
    src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js">
</script>



<script>

document.addEventListener('DOMContentLoaded', function () {

    /* =====================================================
       ELEMENTS
    ====================================================== */

    const gpsStatus =
        document.getElementById('gpsStatus');

    const mapElement =
        document.getElementById('map');

    const panicBtn =
        document.getElementById('panicBtn');

    const hijackBtn =
        document.getElementById('hijackBtn');


    /* =====================================================
       LARAVEL DATA
    ====================================================== */

    const csrfToken =
        '{{ csrf_token() }}';

    const driverName =
        @json(auth()->user()->name);

    const gpsUpdateUrl =
        '{{ route('driver.gps.update') }}';


    /* =====================================================
       ACTIVE DISPATCH
    ====================================================== */

    /*
     * Wala munang active dispatch.
     *
     * Kapag mayroon nang assigned incident,
     * dito natin ilalagay ang coordinates.
     */

    const activeDispatchLocation = null;


    /* =====================================================
       VARIABLES
    ====================================================== */

    let currentPosition = null;

    let gpsInterval = null;

    let gpsRequestInFlight = false;

    let map = null;

    let driverMarker = null;

    let incidentMarker = null;

    let routingControl = null;


    /* =====================================================
       GPS STATUS
    ====================================================== */

    function setGpsStatus(message) {

        if (gpsStatus) {

            gpsStatus.textContent = message;

        }

    }


    /* =====================================================
       GET GPS POSITION
    ====================================================== */

    function getPosition() {

        return new Promise(function (resolve, reject) {

            if (!navigator.geolocation) {

                reject(
                    new Error(
                        'GPS is not supported by this browser.'
                    )
                );

                return;

            }


            navigator.geolocation.getCurrentPosition(

                function (position) {

                    currentPosition = position;

                    resolve(position);

                },

                function (error) {

                    console.error(
                        'GPS error:',
                        error
                    );

                    reject(error);

                },

                {
                    enableHighAccuracy: true,
                    timeout: 30000,
                    maximumAge: 10000
                }

            );

        });

    }


    /* =====================================================
       DRIVER ICON
    ====================================================== */

    function createDriverIcon() {

        return L.divIcon({

            className: 'driver-map-pin',

            html:
                '<div style="' +
                'background:#0d6efd;' +
                'border:3px solid white;' +
                'border-radius:50%;' +
                'width:18px;' +
                'height:18px;' +
                'box-shadow:0 2px 10px rgba(0,0,0,.35);' +
                '"></div>',

            iconSize: [18, 18],

            iconAnchor: [9, 9]

        });

    }


    /* =====================================================
       UPDATE DRIVER MARKER
    ====================================================== */

    function updateDriverMarker(
        latitude,
        longitude
    ) {

        if (!map) {

            return;

        }


        const position = [
            latitude,
            longitude
        ];


        if (!driverMarker) {

            driverMarker =
                L.marker(
                    position,
                    {
                        icon: createDriverIcon()
                    }
                )
                .addTo(map)
                .bindPopup(
                    '<strong>Driver</strong><br>' +
                    escapeHtml(driverName)
                );

        } else {

            driverMarker.setLatLng(position);

        }

    }


    /* =====================================================
       SEND GPS TO LARAVEL
    ====================================================== */

    async function sendLocation() {

        if (
            gpsRequestInFlight ||
            document.hidden
        ) {

            return;

        }


        gpsRequestInFlight = true;


        try {

            setGpsStatus('Getting location...');


            const position =
                await getPosition();


            const latitude =
                position.coords.latitude;

            const longitude =
                position.coords.longitude;


            /*
             * SEND GPS TO LARAVEL
             */

            const response =
                await fetch(
                    gpsUpdateUrl,
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

                        body:
                            JSON.stringify({
                                latitude:
                                    latitude,

                                longitude:
                                    longitude
                            })
                    }
                );


            /*
             * CHECK RESPONSE
             */

            if (!response.ok) {

                setGpsStatus(
                    'Error ' +
                    response.status
                );

                console.error(
                    'GPS server error:',
                    response.status
                );

                return;

            }


            /*
             * GPS IS LIVE
             */

            setGpsStatus('Live');


            /*
             * UPDATE MAP MARKER
             */

            updateDriverMarker(
                latitude,
                longitude
            );


            /*
             * ACTIVE INCIDENT ROUTE
             */

            if (activeDispatchLocation) {

                showIncidentRoute(
                    latitude,
                    longitude
                );

            }


        } catch (error) {

            console.error(
                'GPS update error:',
                error
            );


            if (error.code === 1) {

                setGpsStatus(
                    'Permission needed'
                );

            } else if (error.code === 2) {

                setGpsStatus(
                    'Location unavailable'
                );

            } else if (error.code === 3) {

                setGpsStatus(
                    'GPS timeout'
                );

            } else {

                setGpsStatus(
                    'Unavailable'
                );

            }

        } finally {

            gpsRequestInFlight = false;

        }

    }


    /* =====================================================
       INCIDENT + ROUTE
    ====================================================== */

    function showIncidentRoute(
        driverLat,
        driverLng
    ) {

        if (!activeDispatchLocation) {

            return;

        }


        const incidentLat =
            Number(
                activeDispatchLocation.latitude
            );


        const incidentLng =
            Number(
                activeDispatchLocation.longitude
            );


        if (
            !Number.isFinite(incidentLat) ||
            !Number.isFinite(incidentLng)
        ) {

            return;

        }


        /*
         * INCIDENT MARKER
         */

        if (!incidentMarker) {

            incidentMarker =
                L.marker(
                    [
                        incidentLat,
                        incidentLng
                    ]
                )
                .addTo(map)
                .bindPopup(
                    '<strong>Emergency Incident</strong>'
                );

        } else {

            incidentMarker.setLatLng(
                [
                    incidentLat,
                    incidentLng
                ]
            );

        }


        /*
         * REMOVE OLD ROUTE
         */

        if (routingControl) {

            map.removeControl(
                routingControl
            );

            routingControl = null;

        }


        /*
         * CREATE ROUTE
         */

        if (
            typeof L.Routing !==
            'undefined'
        ) {

            routingControl =
                L.Routing.control({

                    waypoints: [

                        L.latLng(
                            driverLat,
                            driverLng
                        ),

                        L.latLng(
                            incidentLat,
                            incidentLng
                        )

                    ],

                    routeWhileDragging:
                        false,

                    addWaypoints:
                        false,

                    draggableWaypoints:
                        false,

                    fitSelectedRoutes:
                        true,

                    show:
                        false,

                    createMarker:
                        function () {
                            return null;
                        }

                })
                .on(
                    'routingerror',
                    function (error) {

                        console.warn(
                            'Routing error:',
                            error
                        );

                    }
                )
                .addTo(map);

        }


        /*
         * FIT MAP
         */

        map.fitBounds(

            [
                [
                    driverLat,
                    driverLng
                ],

                [
                    incidentLat,
                    incidentLng
                ]
            ],

            {
                padding: [50, 50]
            }

        );

    }


    /* =====================================================
       INITIALIZE MAP
    ====================================================== */

    function initializeMap() {

        if (
            !mapElement ||
            typeof L === 'undefined'
        ) {

            console.error(
                'Leaflet or map element not found.'
            );

            return;

        }


        /*
         * CREATE MAP
         */

        map =
            L.map('map', {
                zoomControl: true
            });


        /*
         * OPEN STREET MAP
         */

        L.tileLayer(

            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',

            {
                maxZoom: 19,

                attribution:
                    '&copy; OpenStreetMap contributors'
            }

        ).addTo(map);


        /*
         * USE CURRENT GPS
         */

        if (currentPosition) {

            const latitude =
                currentPosition.coords.latitude;

            const longitude =
                currentPosition.coords.longitude;


            updateDriverMarker(
                latitude,
                longitude
            );


            if (activeDispatchLocation) {

                showIncidentRoute(
                    latitude,
                    longitude
                );

            } else {

                map.setView(
                    [
                        latitude,
                        longitude
                    ],
                    15
                );

            }

        } else {

            /*
             * NUEVA ECIJA FALLBACK
             */

            map.setView(
                [
                    15.4866,
                    120.9675
                ],
                13
            );

        }


        /*
         * FIX MAP SIZE
         */

        setTimeout(
            function () {

                map.invalidateSize();

            },
            300
        );


        /*
         * RESIZE
         */

        window.addEventListener(
            'resize',
            function () {

                setTimeout(
                    function () {

                        if (map) {

                            map.invalidateSize();

                        }

                    },
                    150
                );

            }
        );

    }


    /* =====================================================
       PANIC / HIJACK
    ====================================================== */

    async function triggerEmergency(
        url,
        label
    ) {

        try {

            let position =
                currentPosition;


            /*
             * GET GPS IF NOT AVAILABLE
             */

            if (!position) {

                position =
                    await getPosition();

            }


            /*
             * SEND EMERGENCY
             */

            const response =
                await fetch(

                    url,

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

                        body:
                            JSON.stringify({

                                latitude:
                                    position.coords.latitude,

                                longitude:
                                    position.coords.longitude

                            })

                    }

                );


            /*
             * RESPONSE
             */

            const data =
                await response
                    .json()
                    .catch(
                        function () {
                            return null;
                        }
                    );


            /*
             * SERVER ERROR
             */

            if (!response.ok) {

                alert(
                    label +
                    ' alert failed: ' +
                    response.status
                );

                return;

            }


            /*
             * SUCCESS
             */

            if (
                data &&
                data.success
            ) {

                alert(
                    label +
                    ' ALERT SENT'
                );

            } else {

                alert(
                    label +
                    ' alert could not be sent.'
                );

            }


        } catch (error) {

            console.error(
                label +
                ' error:',
                error
            );


            alert(
                label +
                ' alert could not be sent. ' +
                'Check location permission and connection.'
            );

        }

    }


    /* =====================================================
       PANIC BUTTON
    ====================================================== */

    if (panicBtn) {

        panicBtn.addEventListener(
            'click',
            async function () {

                if (
                    !confirm(
                        'Trigger PANIC alert?'
                    )
                ) {

                    return;

                }


                await triggerEmergency(
                    '{{ route('driver.panic.trigger') }}',
                    'PANIC'
                );

            }
        );

    }


    /* =====================================================
       HIJACK BUTTON
    ====================================================== */

    if (hijackBtn) {

        hijackBtn.addEventListener(
            'click',
            async function () {

                if (
                    !confirm(
                        'Trigger HIJACK alert?'
                    )
                ) {

                    return;

                }


                await triggerEmergency(
                    '{{ route('driver.hijack.trigger') }}',
                    'HIJACK'
                );

            }
        );

    }


    /* =====================================================
       ESCAPE HTML
    ====================================================== */

    function escapeHtml(value) {

        const div =
            document.createElement('div');


        div.textContent =
            value ?? '';


        return div.innerHTML;

    }


    /* =====================================================
       START DRIVER TRACKING
    ====================================================== */

    async function startDriverTracking() {

        /*
         * INITIAL GPS
         */

        await sendLocation();


        /*
         * INITIALIZE MAP
         */

        initializeMap();


        /*
         * UPDATE EVERY 15 SECONDS
         */

        gpsInterval =
            setInterval(
                sendLocation,
                15000
            );

    }


    /* =====================================================
       VISIBILITY CHANGE
    ====================================================== */

    document.addEventListener(
        'visibilitychange',
        function () {

            if (!document.hidden) {

                sendLocation();

            }

        }
    );


    /* =====================================================
       PAGE HIDE
    ====================================================== */

    window.addEventListener(
        'pagehide',
        function () {

            if (gpsInterval) {

                clearInterval(
                    gpsInterval
                );

                gpsInterval = null;

            }

        },
        {
            once: true
        }
    );


    /* =====================================================
       START
    ====================================================== */

    startDriverTracking();

});

</script>


</body>

</html>