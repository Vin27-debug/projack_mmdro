<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MuniResQ Admin Panel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
    ])

    <style>
        :root {
            --mr-bg: #08172f;
            --mr-surface: rgba(11, 26, 53, 0.96);
            --mr-border: rgba(255, 255, 255, 0.1);
            --mr-text: #eef4ff;
            --mr-muted: rgba(255, 255, 255, 0.72);
            --mr-accent: #3b69ff;
            --mr-danger: #dc3545;
            --mr-success: #1c8e5b;
            --mr-warning: #f8b620;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI Emoji", sans-serif;
            background: radial-gradient(circle at top left, rgba(59, 105, 255, 0.12), transparent 28%),
                linear-gradient(180deg, #071329 0%, #08172f 100%);
            color: var(--mr-text);
        }

        .admin-shell {
            min-height: 100vh;
        }

        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #091c3d 0%, #07172f 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1.25rem 0 1.5rem;
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0 1.25rem 1.5rem;
            margin-bottom: 1rem;
        }

        .admin-brand-mark {
            width: 44px;
            height: 44px;
            border-radius: 1rem;
            display: grid;
            place-items: center;
            background: #0d6efd;
            color: #fff;
            font-weight: 700;
            font-size: 1.15rem;
            box-shadow: 0 16px 30px rgba(13, 110, 253, 0.18);
        }

        .admin-brand-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #fff;
        }

        .admin-brand-subtitle {
            margin: 0;
            font-size: 0.82rem;
            color: var(--mr-muted);
        }

        .admin-nav-title {
            margin: 0 0 0.75rem 1.25rem;
            font-size: 0.76rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.48);
        }

        .admin-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.78);
            padding: 0.9rem 1.25rem;
            margin: 0.15rem 1.25rem;
            border-radius: 1rem;
            transition: all 0.18s ease;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            font-weight: 500;
        }

        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background: rgba(59, 105, 255, 0.16);
            color: #fff;
        }

        .admin-sidebar .nav-link.active {
            box-shadow: inset 0 0 0 1px rgba(59, 105, 255, 0.22);
        }

        .admin-sidebar .nav-link i {
            min-width: 1.4rem;
            font-size: 1.15rem;
        }

        .admin-sidebar .badge {
            min-width: 2.1rem;
        }

        .main-content {
            padding: 1.75rem 1.75rem 2.5rem;
            min-height: 100vh;
        }

        .admin-card,
        .eoc-card {
            background: rgba(7, 18, 38, 0.88);
            border: 1px solid rgba(59, 105, 255, 0.18);
            border-radius: 1.5rem;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(18px);
        }

        .admin-header-panel,
        .eoc-header-panel {
            background: linear-gradient(135deg, rgba(13, 28, 60, 0.96), rgba(8, 20, 44, 0.94));
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .card.border-0.shadow-sm {
            background: rgba(7, 18, 38, 0.88);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.5rem;
            box-shadow: 0 18px 35px rgba(0, 0, 0, 0.16);
        }

        .section-heading {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
            color: #ffffff;
        }

        .section-excerpt {
            margin-bottom: 0;
            color: rgba(255, 255, 255, 0.72);
        }

        .admin-summary-card {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.25rem;
        }

        .admin-stat-card {
            border-radius: 1.35rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .card {
            background: rgba(7, 18, 38, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.35rem;
            box-shadow: 0 18px 35px rgba(0, 0, 0, 0.16);
        }

        .card .card-body,
        .card .card-header,
        .card .card-footer,
        .card .list-group-item {
            color: rgba(255, 255, 255, 0.92);
        }

        .form-control,
        .form-select,
        .form-text {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: rgba(255, 255, 255, 0.92);
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(59, 105, 255, 0.5);
            box-shadow: 0 0 0 0.25rem rgba(59, 105, 255, 0.12);
        }

        .alert {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.12);
            color: rgba(255, 255, 255, 0.92);
        }

        .alert-light,
        .bg-light {
            background-color: rgba(255, 255, 255, 0.08) !important;
            color: rgba(255, 255, 255, 0.92) !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
        }

        .text-muted {
            color: rgba(255, 255, 255, 0.72) !important;
        }

        .table-light th,
        .table-light td,
        .table-dark th,
        .table-dark td {
            background-color: rgba(255, 255, 255, 0.06) !important;
            color: rgba(255, 255, 255, 0.88) !important;
        }

        .badge.bg-light {
            background-color: rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
        }

        .bg-primary-subtle,
        .bg-success-subtle,
        .bg-warning-subtle,
        .bg-danger-subtle,
        .bg-info-subtle,
        .bg-dark-subtle,
        .bg-light {
            background-color: rgba(255, 255, 255, 0.06) !important;
            color: rgba(255, 255, 255, 0.92) !important;
        }

        .bg-primary-subtle {
            background-color: rgba(13, 110, 253, 0.14) !important;
            color: #ffffff !important;
        }

        .bg-success-subtle {
            background-color: rgba(25, 135, 84, 0.14) !important;
            color: #ffffff !important;
        }

        .bg-warning-subtle {
            background-color: rgba(255, 193, 7, 0.16) !important;
            color: #111111 !important;
        }

        .bg-danger-subtle {
            background-color: rgba(220, 53, 69, 0.14) !important;
            color: #ffffff !important;
        }

        .bg-dark-subtle {
            background-color: rgba(33, 37, 41, 0.14) !important;
            color: #ffffff !important;
        }

        .bg-gradient,
        .card.bg-gradient {
            background-image: none !important;
            background-color: rgba(255, 255, 255, 0.04) !important;
            box-shadow: 0 18px 35px rgba(0, 0, 0, 0.18) !important;
        }

        .table-light th,
        .table-light td {
            background-color: rgba(255, 255, 255, 0.06) !important;
            color: rgba(255, 255, 255, 0.88) !important;
        }

        .list-group-item {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.9);
        }

        .table thead th {
            color: rgba(255, 255, 255, 0.8);
            border-bottom: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(11, 26, 49, 0.92);
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .table {
            background-color: transparent !important;
        }

        .table thead th {
            color: rgba(255, 255, 255, 0.8);
            background: rgba(11, 26, 49, 0.92) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.14);
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .table tbody tr {
            background: transparent !important;
        }

        .table tbody tr:nth-child(odd) {
            background: rgba(255, 255, 255, 0.02) !important;
        }

        .table tbody tr:hover,
        .table-hover tbody tr:hover {
            background: rgba(255, 255, 255, 0.06) !important;
        }

        .table th,
        .table td {
            border-color: rgba(255, 255, 255, 0.08) !important;
            color: rgba(255, 255, 255, 0.88) !important;
            background-color: transparent !important;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.65rem 0.75rem;
        }

        .card-header {
            background: rgba(11, 26, 49, 0.92);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .progress {
            background-color: rgba(255, 255, 255, 0.08);
        }

        .progress-bar {
            box-shadow: none;
        }

        .min-touch-target {
            min-height: 44px;
        }

        :where(button, a, input, select, textarea):focus-visible {
            outline: 3px solid rgba(82, 180, 255, 0.85);
            outline-offset: 2px;
        }

        @media (max-width: 991px) {
            .admin-sidebar {
                min-height: auto;
            }

            .main-content {
                padding: 1.25rem;
            }
        }

        @media (max-width: 767px) {
            .admin-sidebar {
                position: relative;
            }

            .admin-sidebar .admin-brand,
            .admin-sidebar .admin-nav-title {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .admin-sidebar .nav-link {
                margin-left: 1rem;
                margin-right: 1rem;
            }
        }
    </style>
</head>

@php
use Illuminate\Support\Facades\Route;

$adminRoute = function ($name) {
return Route::has($name) ? route($name) : '#';
};
@endphp

<body class="admin-shell">

    <div class="container-fluid admin-shell">

        <div class="row gx-0">

            <!-- SIDEBAR -->

            <div class="col-12 col-xl-2">
                <aside class="admin-sidebar">
                    <div class="admin-brand">
                        <div class="admin-brand-mark">M</div>
                        <div>
                            <h4 class="admin-brand-title mb-1">MuniResQ</h4>
                            <p class="admin-brand-subtitle mb-0">Admin Command</p>
                        </div>
                    </div>

                    <div class="admin-nav-title">Operations</div>
                    <nav class="nav flex-column mb-4">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    <a href="{{ route('admin.audit-logs.index') }}"
   class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
    <i class="bi bi-journal-text"></i> Audit Logs
</a>
                        <a href="{{ $adminRoute('admin.notifications.index') }}" class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                            <i class="bi bi-bell"></i> Notifications
                            <span class="badge bg-danger float-end" data-unread-badge>{{ $unreadNotifications ?? 0 }}</span>
                        </a>
                        <a href="{{ url('/admin/incidents') }}" class="nav-link {{ request()->is('admin/incidents*') ? 'active' : '' }}">
                            <i class="bi bi-exclamation-triangle"></i> Incidents
                        </a>
                        <a href="{{ $adminRoute('admin.dispatches.index') }}" class="nav-link {{ request()->routeIs('admin.dispatches.*') ? 'active' : '' }}">
                            <i class="bi bi-geo-alt"></i> Dispatch Center
                        </a>
                        <a href="{{ $adminRoute('admin.gps.monitoring') }}" class="nav-link {{ request()->routeIs('admin.gps.monitoring') ? 'active' : '' }}">
                            <i class="bi bi-geo-fill"></i> GPS Monitoring
                        </a>
                        <a href="{{ $adminRoute('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text"></i> Incident Reports
                        </a>
                        <a href="{{ $adminRoute('admin.reports.pdf.view') }}" class="nav-link {{ request()->routeIs('admin.reports.pdf.view') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-pdf"></i> PDF Reports
                        </a>
                        <a href="{{ $adminRoute('admin.maintenance.index') }}" class="nav-link {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}">
                            <i class="bi bi-tools"></i> Vehicle Maintenance
                        </a>
                        <a href="{{ $adminRoute('admin.vulnerable-areas.index') }}" class="nav-link {{ request()->routeIs('admin.vulnerable-areas.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i> Vulnerable Areas
                        </a>
                        <a href="{{ $adminRoute('admin.response-equipment.index') }}" class="nav-link {{ request()->routeIs('admin.response-equipment.*') ? 'active' : '' }}">
                            <i class="bi bi-box-seam"></i> Equipment Inventory
                        </a>
                        <a href="{{ $adminRoute('admin.operations.center') }}" class="nav-link {{ request()->routeIs('admin.operations.center') ? 'active' : '' }}">
                            <i class="bi bi-target"></i> Operations Center
                        </a>
                        <a href="{{ $adminRoute('admin.reports.center') }}" class="nav-link {{ request()->routeIs('admin.reports.center') ? 'active' : '' }}">
                            <i class="bi bi-graph-up"></i> Reports Center
                        </a>
                        <a href="{{ $adminRoute('admin.reports.response-time') }}" class="nav-link {{ request()->routeIs('admin.reports.response-time') ? 'active' : '' }}">
                            <i class="bi bi-stopwatch"></i> Response Time
                        </a>
                    </nav>
                    <form method="POST" action="{{ route('logout') }}" class="px-3 mt-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-light w-100 d-flex align-items-center justify-content-center gap-2 rounded-pill">
                            <i class="bi bi-box-arrow-right"></i>
                            Logout
                        </button>
                    </form>
                </aside>
            </div>

            <!-- CONTENT -->

            <div class="col-12 col-xl-10">
                <main class="main-content">
                    @yield('content')
                </main>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const badge = document.querySelector('[data-unread-badge]');

            if (!badge) {
                return;
            }

            let badgeRequest = null;
            let badgeInterval = null;

            const updateBadge = () => {
                if (document.hidden || badgeRequest) {
                    return;
                }

                badgeRequest = fetch('{{ $adminRoute("admin.notifications.unread-count") }}', {
                        headers: {
                            'Accept': 'application/json'
                        },
                        cache: 'no-store'
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Notification request failed: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        badge.textContent = data.unread_count ?? 0;
                    })
                    .catch(() => {
                        badge.setAttribute('aria-label', 'Notifications unavailable');
                    })
                    .finally(() => {
                        badgeRequest = null;
                    });
            };

            updateBadge();
            badgeInterval = setInterval(updateBadge, 15000);
            document.addEventListener('visibilitychange', updateBadge);
            window.addEventListener('pagehide', () => clearInterval(badgeInterval), {
                once: true
            });
        });
    </script>

</body>

</html>