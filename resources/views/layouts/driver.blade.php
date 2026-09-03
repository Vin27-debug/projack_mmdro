<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'MuniResQ Driver')
    </title>


    {{-- =====================================================
         BOOTSTRAP CSS
    ====================================================== --}}

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    {{-- =====================================================
         BOOTSTRAP ICONS
    ====================================================== --}}

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">


    {{-- =====================================================
         LEAFLET CSS
    ====================================================== --}}

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        crossorigin="">


    {{-- =====================================================
         VITE
    ====================================================== --}}

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
    ])


    {{-- =====================================================
         DRIVER LAYOUT CSS
    ====================================================== --}}

    <style>
        /* =====================================================
           BASIC RESET
        ====================================================== */

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            background: #071329;
            color: #eef4ff;

            font-family:
                Inter,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        a {
            text-decoration: none;
        }


        /* =====================================================
           APP
        ====================================================== */

        .driver-app-shell {
            min-height: 100vh;
            width: 100%;
        }


        /* =====================================================
           DESKTOP SIDEBAR
        ====================================================== */

        .sidebar-driver {
            width: 280px;
            min-width: 280px;
            min-height: 100vh;

            background:
                linear-gradient(180deg,
                    #06243a 0%,
                    #0b3658 100%);

            color: #fff;
        }


        /* =====================================================
           BRAND
        ====================================================== */

        .driver-brand {
            border-bottom:
                1px solid rgba(255, 255, 255, .08);
        }

        .driver-brand-icon {
            width: 44px;
            height: 44px;

            border-radius: 50%;

            background:
                rgba(255, 255, 255, .14);

            display: flex;
            align-items: center;
            justify-content: center;

            flex-shrink: 0;
        }


        /* =====================================================
           DRIVER PROFILE
        ====================================================== */

        .driver-profile {
            border-bottom:
                1px solid rgba(255, 255, 255, .08);
        }

        .driver-avatar {
            position: relative;

            width: 70px;
            height: 70px;

            border-radius: 50%;

            background:
                rgba(255, 255, 255, .14);

            display: grid;
            place-items: center;

            font-size: 1.8rem;
        }

        .driver-status-dot {
            position: absolute;

            width: 16px;
            height: 16px;

            right: 4px;
            bottom: 4px;

            border-radius: 50%;

            border:
                2px solid #06243a;

            background: #20c997;
        }


        /* =====================================================
           NAVIGATION
        ====================================================== */

        .sidebar-nav {
            padding: 1rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;

            gap: .8rem;

            width: 100%;

            padding: .85rem .95rem;

            margin-bottom: .35rem;

            border-radius: .9rem;

            color:
                rgba(255, 255, 255, .88);

            transition:
                background-color .2s ease,
                color .2s ease,
                transform .15s ease;
        }

        .sidebar-link:hover {
            background:
                rgba(255, 255, 255, .10);

            color: #fff;

            transform:
                translateX(2px);
        }

        .sidebar-link.active {
            background:
                rgba(255, 255, 255, .16);

            color: #fff;
        }

        .sidebar-link i {
            width: 28px;
            text-align: center;
        }


        /* =====================================================
           LOGOUT
        ====================================================== */

        .driver-logout {
            border-top:
                1px solid rgba(255, 255, 255, .08);
        }


        /* =====================================================
           MAIN CONTENT
        ====================================================== */

        .content-area {
            min-width: 0;
            min-height: 100vh;

            overflow-x: hidden;

            background:
                radial-gradient(circle at top left,
                    rgba(59, 105, 255, .12),
                    transparent 28%),

                linear-gradient(180deg,
                    #071329 0%,
                    #08172f 100%);
        }

        .driver-page-content {
            width: 100%;
        }


        /* =====================================================
           MOBILE HEADER
        ====================================================== */

        .driver-mobile-header {
            min-height: 52px;
            padding: .35rem 0;

            position: relative;
            z-index: 100;
        }

        .mobile-nav-toggle {
            min-height: 46px;

            border-radius: 999px;

            position: relative;
            z-index: 2001;

            cursor: pointer;
        }


        /* =====================================================
           CUSTOM MOBILE SIDEBAR

           IMPORTANT:
           NO BOOTSTRAP OFFCANVAS HERE
        ====================================================== */

        .driver-mobile-menu {

            position: fixed;

            top: 0;
            left: 0;

            width: 280px;
            max-width: 85vw;

            height: 100vh;

            background:
                linear-gradient(180deg,
                    #06243a 0%,
                    #0b3658 100%);

            color: #fff;

            z-index: 3000;

            overflow-y: auto;

            transform: translateX(-110%);

            transition:
                transform .25s ease;

            box-shadow:
                8px 0 30px rgba(0, 0, 0, .35);
        }


        /* OPEN STATE */

        .driver-mobile-menu.is-open {
            transform: translateX(0);
        }


        /* =====================================================
           MOBILE MENU OVERLAY
        ====================================================== */

        .driver-mobile-overlay {

            position: fixed;

            top: 0;
            left: 0;

            width: 100%;
            height: 100vh;

            background:
                rgba(0, 0, 0, .55);

            z-index: 2999;

            opacity: 0;

            visibility: hidden;

            transition:
                opacity .25s ease,
                visibility .25s ease;
        }


        .driver-mobile-overlay.is-open {

            opacity: 1;

            visibility: visible;
        }


        /* =====================================================
           PREVENT BODY SCROLL WHEN MENU OPEN
        ====================================================== */

        body.mobile-menu-open {
            overflow: hidden;
        }


        /* =====================================================
           MOBILE MENU HEADER
        ====================================================== */

        .driver-mobile-menu-header {

            min-height: 70px;

            padding: 1rem;

            display: flex;

            align-items: center;

            justify-content: space-between;

            background:
                #06243a;

            border-bottom:
                1px solid rgba(255, 255, 255, .08);
        }


        .driver-mobile-menu-title {
            font-weight: 700;
            color: #fff;
        }


        .driver-mobile-menu-subtitle {
            color:
                rgba(255, 255, 255, .60);

            font-size: .75rem;
        }


        .driver-mobile-close {

            width: 40px;
            height: 40px;

            border: 0;

            border-radius: 50%;

            background:
                rgba(255, 255, 255, .10);

            color: #fff;

            display: flex;

            align-items: center;

            justify-content: center;

            cursor: pointer;

            font-size: 1.25rem;
        }

        .driver-mobile-close:hover {

            background:
                rgba(255, 255, 255, .18);
        }


        /* =====================================================
           MOBILE PROFILE
        ====================================================== */

        .driver-mobile-profile {

            padding: 1rem;

            background:
                rgba(0, 0, 0, .10);

            border-bottom:
                1px solid rgba(255, 255, 255, .08);
        }


        /* =====================================================
           MOBILE NAV
        ====================================================== */

        .driver-mobile-nav {

            padding: 1rem;
        }


        .driver-mobile-nav .sidebar-link {

            color:
                rgba(255, 255, 255, .88);

            cursor: pointer;
        }


        .driver-mobile-nav .sidebar-link:hover {

            background:
                rgba(255, 255, 255, .10);

            color: #fff;

            transform: translateX(2px);
        }


        .driver-mobile-nav .sidebar-link.active {

            background:
                rgba(255, 255, 255, .16);

            color: #fff;
        }


        /* =====================================================
           MOBILE LOGOUT
        ====================================================== */

        .driver-mobile-logout {

            padding: 1rem;
        }


        /* =====================================================
           CARDS
        ====================================================== */

        .driver-layout .card {

            background:
                rgba(7, 18, 38, .94);

            color:
                rgba(255, 255, 255, .92);

            border:
                1px solid rgba(255, 255, 255, .08);

            border-radius:
                1.25rem;
        }

        .driver-layout .card-header {

            background:
                rgba(11, 26, 49, .94);

            color: #fff;

            border-bottom:
                1px solid rgba(255, 255, 255, .08);
        }

        .driver-layout .card-body {

            color:
                rgba(255, 255, 255, .92);
        }


        /* =====================================================
           TEXT
        ====================================================== */

        .driver-layout .text-muted {

            color:
                rgba(255, 255, 255, .68) !important;
        }

        .driver-layout .text-white-50 {

            color:
                rgba(255, 255, 255, .65) !important;
        }


        /* =====================================================
           FORMS
        ====================================================== */

        .driver-layout .form-control,
        .driver-layout .form-select,
        .driver-layout .input-group-text {

            background:
                rgba(255, 255, 255, .06);

            border-color:
                rgba(255, 255, 255, .14);

            color: #eef4ff;
        }

        .driver-layout .form-control:focus,
        .driver-layout .form-select:focus {

            background:
                rgba(255, 255, 255, .08);

            border-color:
                rgba(13, 110, 253, .9);

            color: #fff;

            box-shadow:
                0 0 0 .2rem rgba(13, 110, 253, .20);
        }

        .driver-layout .form-control::placeholder {

            color:
                rgba(255, 255, 255, .55);
        }


        /* =====================================================
           TABLE
        ====================================================== */

        .driver-layout .table {

            --bs-table-bg: transparent;

            --bs-table-color: #eef4ff;
        }

        .driver-layout .table th,
        .driver-layout .table td {

            background:
                rgba(255, 255, 255, .04) !important;

            color:
                #eef4ff !important;

            border-color:
                rgba(255, 255, 255, .08) !important;
        }


        /* =====================================================
           MAP
        ====================================================== */

        .driver-layout .map-shell {

            width: 100%;

            min-height: 360px;

            overflow: hidden;

            border-radius: 1rem;

            border:
                1px solid rgba(255, 255, 255, .08);
        }

        .driver-layout #map {

            width: 100%;

            height: 360px;
        }


        /* =====================================================
           BUTTONS
        ====================================================== */

        .driver-action-btn {

            min-height: 46px;

            border-radius: 999px;

            font-weight: 600;
        }


        /* =====================================================
           LEAFLET
        ====================================================== */

        .leaflet-container {

            background: #172033;

            font-family: inherit;
        }

        .leaflet-control-attribution {

            font-size: 10px;
        }


        /* =====================================================
           DESKTOP
        ====================================================== */

        @media (min-width: 992px) {

            .sidebar-driver {

                display: flex !important;
            }

            .driver-mobile-header {

                display: none !important;
            }

            .driver-mobile-menu {

                display: none !important;
            }

            .driver-mobile-overlay {

                display: none !important;
            }
        }


        /* =====================================================
           MOBILE / TABLET
        ====================================================== */

        @media (max-width: 991.98px) {

            .sidebar-driver {

                display: none !important;
            }
        }


        /* =====================================================
           TABLET MAP
        ====================================================== */

        @media (min-width: 768px) {

            .driver-layout #map {

                height: 430px;
            }

            .driver-layout .map-shell {

                min-height: 430px;
            }
        }


        /* =====================================================
           LARGE SCREEN MAP
        ====================================================== */

        @media (min-width: 1200px) {

            .driver-layout #map {

                height: 480px;
            }

            .driver-layout .map-shell {

                min-height: 480px;
            }
        }


        /* =====================================================
           SMALL MOBILE
        ====================================================== */

        @media (max-width: 575.98px) {

            .driver-layout .content-area>.container-fluid {

                padding: .75rem !important;
            }

            .mobile-nav-toggle {

                min-height: 48px;
            }

            .driver-mobile-header {

                padding-top: .25rem;
            }
        }
    </style>


    @stack('styles')

</head>


<body class="driver-layout">


    {{-- =====================================================
         MAIN APP
    ====================================================== --}}

    <div class="driver-app-shell d-flex flex-column flex-lg-row">


        {{-- =====================================================
             DESKTOP SIDEBAR
        ====================================================== --}}

        <aside
            class="sidebar-driver d-none d-lg-flex flex-column">


            {{-- BRAND --}}

            <div
                class="driver-brand p-3 d-flex align-items-center gap-2">

                <div class="driver-brand-icon">

                    <i class="bi bi-hospital text-white fs-4"></i>

                </div>

                <div>

                    <div class="fw-bold text-white">
                        MuniResQ
                    </div>

                    <small class="text-white-50">
                        Driver Operations
                    </small>

                </div>

            </div>


            {{-- PROFILE --}}

            <div
                class="driver-profile p-3 text-center">

                <div
                    class="driver-avatar mx-auto mb-2">

                    <i class="bi bi-person-fill"></i>

                    <span
                        class="driver-status-dot"
                        title="Available">
                    </span>

                </div>

                <div class="fw-semibold text-white">

                    {{ ($user ?? auth()->user())?->name ?? 'Driver' }}

                </div>

                @if(($user ?? auth()->user())?->driver)

                <div class="text-white-50 small mt-1">

                    Badge:
                    {{ auth()->user()->driver->badge_id }}

                </div>

                @endif

                <div class="mt-2">

                    <span class="badge bg-success">
                        Available
                    </span>

                </div>

            </div>


            {{-- NAVIGATION --}}

            <nav class="nav flex-column sidebar-nav">


                <a
                    href="{{ route('driver.dashboard') }}"
                    class="sidebar-link
                    {{ request()->routeIs('driver.dashboard') ? 'active' : '' }}">

                    <i class="bi bi-speedometer2 fs-5"></i>

                    <span>
                        Dashboard
                    </span>

                </a>


                <a
                    href="{{ route('driver.navigation') }}"
                    class="sidebar-link
                    {{ request()->routeIs('driver.navigation') ? 'active' : '' }}">

                    <i class="bi bi-geo-alt-fill fs-5"></i>

                    <span>
                        Navigation
                    </span>

                </a>


                <a
                    href="{{ route('driver.assignment') }}"
                    class="sidebar-link
                    {{ request()->routeIs('driver.assignment') ? 'active' : '' }}">

                    <i class="bi bi-list-check fs-5"></i>

                    <span>
                        My Assignment
                    </span>

                </a>


                <a
                    href="{{ route('driver.report.create') }}"
                    class="sidebar-link
                    {{ request()->routeIs('driver.report.*') ? 'active' : '' }}">

                    <i class="bi bi-file-earmark-medical fs-5"></i>

                    <span>
                        Reports
                    </span>

                </a>


                <a
                    href="{{ route('driver.history') }}"
                    class="sidebar-link
                    {{ request()->routeIs('driver.history') ? 'active' : '' }}">

                    <i class="bi bi-clock-history fs-5"></i>

                    <span>
                        Dispatch History
                    </span>

                </a>


                <a
                    href="{{ route('driver.settings') }}"
                    class="sidebar-link
                    {{ request()->routeIs('driver.settings') ? 'active' : '' }}">

                    <i class="bi bi-gear fs-5"></i>

                    <span>
                        Settings
                    </span>

                </a>


                <a
                    href="{{ url('/profile') }}"
                    class="sidebar-link
                    {{ request()->is('profile*') ? 'active' : '' }}">

                    <i class="bi bi-person-circle fs-5"></i>

                    <span>
                        Profile
                    </span>

                </a>

            </nav>


            {{-- LOGOUT --}}

            <div
                class="driver-logout mt-auto p-3">

                <form
                    method="POST"
                    action="{{ route('logout') }}">

                    @csrf

                    <button
                        type="submit"
                        class="btn btn-danger w-100
                        d-flex align-items-center
                        justify-content-center
                        gap-2 rounded-pill">

                        <i class="bi bi-box-arrow-right"></i>

                        <span>
                            Logout
                        </span>

                    </button>

                </form>

            </div>

        </aside>


        {{-- =====================================================
             CUSTOM MOBILE MENU
        ====================================================== --}}

        <div
            id="driverMobileMenu"
            class="driver-mobile-menu">


            {{-- MOBILE MENU HEADER --}}

            <div class="driver-mobile-menu-header">

                <div>

                    <div class="driver-mobile-menu-title">
                        MuniResQ Driver
                    </div>

                    <div class="driver-mobile-menu-subtitle">
                        Driver Operations
                    </div>

                </div>


                <button
                    type="button"
                    id="driverMobileClose"
                    class="driver-mobile-close"
                    aria-label="Close menu">

                    <i class="bi bi-x-lg"></i>

                </button>

            </div>


            {{-- MOBILE PROFILE --}}

            <div class="driver-mobile-profile">

                <div
                    class="d-flex align-items-center gap-3">


                    <div
                        class="driver-avatar"
                        style="
                            width:55px;
                            height:55px;
                            font-size:1.4rem;
                        ">

                        <i class="bi bi-person-fill"></i>

                        <span
                            class="driver-status-dot"
                            style="
                                width:14px;
                                height:14px;
                                right:2px;
                                bottom:2px;
                            ">
                        </span>

                    </div>


                    <div>

                        <div class="fw-semibold text-white">

                            {{ ($user ?? auth()->user())?->name ?? 'Driver' }}

                        </div>


                        <div class="small text-white-50">

                            Driver

                        </div>


                        @if(($user ?? auth()->user())?->driver)

                        <div class="small text-white-50">

                            Badge:
                            {{ auth()->user()->driver->badge_id }}

                        </div>

                        @endif


                        <span class="badge bg-success mt-1">

                            Available

                        </span>

                    </div>

                </div>

            </div>


            {{-- MOBILE NAVIGATION --}}

            <nav class="driver-mobile-nav">


                <a
                    href="{{ route('driver.dashboard') }}"
                    class="sidebar-link
                    {{ request()->routeIs('driver.dashboard') ? 'active' : '' }}">

                    <i class="bi bi-speedometer2 fs-5"></i>

                    <span>
                        Dashboard
                    </span>

                </a>


                <a
                    href="{{ route('driver.navigation') }}"
                    class="sidebar-link
                    {{ request()->routeIs('driver.navigation') ? 'active' : '' }}">

                    <i class="bi bi-geo-alt-fill fs-5"></i>

                    <span>
                        Navigation
                    </span>

                </a>


                <a
                    href="{{ route('driver.assignment') }}"
                    class="sidebar-link
                    {{ request()->routeIs('driver.assignment') ? 'active' : '' }}">

                    <i class="bi bi-list-check fs-5"></i>

                    <span>
                        My Assignment
                    </span>

                </a>


                <a
                    href="{{ route('driver.report.create') }}"
                    class="sidebar-link
                    {{ request()->routeIs('driver.report.*') ? 'active' : '' }}">

                    <i class="bi bi-file-earmark-medical fs-5"></i>

                    <span>
                        Reports
                    </span>

                </a>


                <a
                    href="{{ route('driver.history') }}"
                    class="sidebar-link
                    {{ request()->routeIs('driver.history') ? 'active' : '' }}">

                    <i class="bi bi-clock-history fs-5"></i>

                    <span>
                        Dispatch History
                    </span>

                </a>


                <a
                    href="{{ route('driver.settings') }}"
                    class="sidebar-link
                    {{ request()->routeIs('driver.settings') ? 'active' : '' }}">

                    <i class="bi bi-gear fs-5"></i>

                    <span>
                        Settings
                    </span>

                </a>


                <a
                    href="{{ url('/profile') }}"
                    class="sidebar-link
                    {{ request()->is('profile*') ? 'active' : '' }}">

                    <i class="bi bi-person-circle fs-5"></i>

                    <span>
                        Profile
                    </span>

                </a>

            </nav>


            {{-- MOBILE LOGOUT --}}

            <div class="driver-mobile-logout">

                <form
                    method="POST"
                    action="{{ route('logout') }}">

                    @csrf

                    <button
                        type="submit"
                        class="btn btn-danger
                        w-100 rounded-pill">

                        <i
                            class="bi bi-box-arrow-right me-2">
                        </i>

                        Logout

                    </button>

                </form>

            </div>

        </div>


        {{-- =====================================================
             MOBILE OVERLAY
        ====================================================== --}}

        <div
            id="driverMobileOverlay"
            class="driver-mobile-overlay">
        </div>


        {{-- =====================================================
             MAIN CONTENT
        ====================================================== --}}

        <main
            class="content-area flex-grow-1">


            <div
                class="container-fluid p-3 p-lg-4">


                {{-- =================================================
                     MOBILE HEADER
                ================================================== --}}

                <div
                    class="driver-mobile-header
                    d-flex
                    justify-content-between
                    align-items-center
                    mb-3
                    d-lg-none">


                    {{-- MENU BUTTON --}}

                    <button
                        type="button"
                        id="driverMobileOpen"
                        class="btn btn-primary mobile-nav-toggle">

                        <i class="bi bi-list fs-5"></i>

                        <span>
                            Menu
                        </span>

                    </button>


                    {{-- MOBILE TITLE --}}

                    <div class="text-end">

                        <div class="fw-semibold">

                            MuniResQ

                        </div>

                        <div class="small text-muted">

                            Driver Operations

                        </div>

                        <div class="small text-muted">

                            {{ ($user ?? auth()->user())?->name ?? 'Driver' }}

                            ·

                            Available

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     PAGE CONTENT
                ================================================== --}}

                <div class="driver-page-content">

                    @yield('content')

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
         PAGE SCRIPTS
    ====================================================== --}}

    @yield('scripts')

    @stack('scripts')


    {{-- =====================================================
         CUSTOM MOBILE MENU JAVASCRIPT
    ====================================================== --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const menu =
                document.getElementById('driverMobileMenu');

            const overlay =
                document.getElementById('driverMobileOverlay');

            const openButton =
                document.getElementById('driverMobileOpen');

            const closeButton =
                document.getElementById('driverMobileClose');


            /* =================================================
               CHECK ELEMENTS
            ================================================= */

            if (
                !menu ||
                !overlay ||
                !openButton ||
                !closeButton
            ) {

                console.error(
                    'MuniResQ mobile menu elements not found.'
                );

                return;
            }


            /* =================================================
               OPEN MENU
            ================================================= */

            function openMobileMenu() {

                menu.classList.add('is-open');

                overlay.classList.add('is-open');

                document.body.classList.add(
                    'mobile-menu-open'
                );

            }


            /* =================================================
               CLOSE MENU
            ================================================= */

            function closeMobileMenu() {

                menu.classList.remove('is-open');

                overlay.classList.remove('is-open');

                document.body.classList.remove(
                    'mobile-menu-open'
                );

            }


            /* =================================================
               MENU BUTTON
            ================================================= */

            openButton.addEventListener(
                'click',
                function(event) {

                    event.preventDefault();

                    event.stopPropagation();

                    openMobileMenu();

                }
            );


            /* =================================================
               CLOSE BUTTON
            ================================================= */

            closeButton.addEventListener(
                'click',
                function(event) {

                    event.preventDefault();

                    event.stopPropagation();

                    closeMobileMenu();

                }
            );


            /* =================================================
               OVERLAY CLICK
            ================================================= */

            overlay.addEventListener(
                'click',
                function() {

                    closeMobileMenu();

                }
            );


            /* =================================================
               CLOSE WHEN NAVIGATION IS CLICKED
            ================================================= */

            menu.querySelectorAll(
                'a.sidebar-link'
            ).forEach(function(link) {

                link.addEventListener(
                    'click',
                    function() {

                        closeMobileMenu();

                    }
                );

            });


            /* =================================================
               ESC KEY
            ================================================= */

            document.addEventListener(
                'keydown',
                function(event) {

                    if (
                        event.key === 'Escape'
                    ) {

                        closeMobileMenu();

                    }

                }
            );


            /* =================================================
               WINDOW RESIZE
            ================================================= */

            window.addEventListener(
                'resize',
                function() {

                    if (
                        window.innerWidth >= 992
                    ) {

                        closeMobileMenu();

                    }

                }
            );

        });
    </script>


</body>

</html>