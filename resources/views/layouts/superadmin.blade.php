<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MuniResQ Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .super-shell {
            min-height: 100vh;
            background: radial-gradient(circle at top left, rgba(59, 105, 255, 0.12), transparent 28%),
                linear-gradient(180deg, #071329 0%, #08172f 100%);
            color: #eef4ff;
        }

        .main-content {
            padding: 1.75rem 1.75rem 2.5rem;
        }

        .super-sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #091c3d 0%, #07172f 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1.25rem 0 1.5rem;
        }

        .super-brand-title,
        .super-brand-subtitle,
        .super-nav-title,
        .nav-link,
        .card,
        .table th,
        .table td,
        .section-heading,
        .section-excerpt,
        .eoc-panel-title,
        .eoc-title,
        .eoc-panel-subtitle,
        .badge-status,
        .text-white-50,
        .text-muted {
            color: #eef4ff;
        }

        .text-white-50 {
            color: rgba(255, 255, 255, 0.72) !important;
        }

        .text-muted {
            color: rgba(255, 255, 255, 0.72) !important;
        }

        .super-brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0 1.25rem 1.5rem;
        }

        .super-brand-mark {
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

        .super-brand-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .super-brand-subtitle {
            margin: 0;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.72);
        }

        .super-nav-title {
            margin: 0 0 0.75rem 1.25rem;
            font-size: 0.75rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.52);
        }

        .super-sidebar .nav-link {
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

        .super-sidebar .nav-link:hover,
        .super-sidebar .nav-link.active {
            background: rgba(59, 105, 255, 0.16);
            color: #fff;
        }

        .super-sidebar .nav-link.active {
            box-shadow: inset 0 0 0 1px rgba(59, 105, 255, 0.22);
        }

        .eoc-title,
        .eoc-panel-title {
            letter-spacing: -0.02em;
        }

        .eoc-card {
            min-height: 100%;
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.55rem 1rem;
            border-radius: 999px;
            background: rgba(25, 135, 84, 0.16);
            color: #c9f7d6;
            font-size: 0.85rem;
        }

        .badge-status.live {
            background: rgba(220, 53, 69, 0.14);
            color: #ffdddd;
        }

        .badge-status.ready {
            background: rgba(13, 110, 253, 0.18);
            color: #ffffff;
        }

        .eoc-card,
        .admin-card {
            background: rgba(7, 18, 38, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.5rem;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.22);
            backdrop-filter: blur(18px);
        }

        .eoc-panel-title,
        .eoc-title {
            font-size: 0.92rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .eoc-panel-subtitle {
            color: rgba(255, 255, 255, 0.72);
        }

        .card {
            background: rgba(7, 18, 38, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.35rem;
            box-shadow: 0 18px 35px rgba(0, 0, 0, 0.16);
        }

        .card-body,
        .card-header,
        .card-footer,
        .table th,
        .table td {
            color: rgba(255, 255, 255, 0.92);
        }

        .card-header {
            background: rgba(11, 26, 49, 0.92);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .table {
            background-color: transparent !important;
        }

        .table thead th {
            background: rgba(11, 26, 49, 0.92) !important;
            color: #ffffff !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.14) !important;
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

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #ffffff;
        }

        .btn-outline-primary {
            color: #0d6efd;
            border-color: rgba(13, 110, 253, 0.6);
        }

        .btn-outline-primary:hover {
            background-color: rgba(13, 110, 253, 0.08);
        }

        .btn-outline-light {
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.6);
        }

        .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .page-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }

        .page-header .page-title {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffffff;
        }

        .page-header .page-subtitle {
            margin: 0;
            color: rgba(255, 255, 255, 0.72);
        }

        .page-back-button {
            min-width: 160px;
        }

        .form-control,
        .form-select {
            background: rgba(255, 255, 255, 0.05);
            color: #eef4ff;
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: none;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(13, 110, 253, 0.4);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.12);
        }

        .table thead th {
            background: rgba(11, 26, 49, 0.92) !important;
            color: #ffffff !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.14) !important;
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
            background-color: transparent !important;
        }

        /* =========================================
   SUPER ADMIN SIDEBAR NAVIGATION
========================================= */

        .superadmin-nav-link {
            display: flex;
            align-items: center;
            gap: 14px;

            width: 100%;
            padding: 14px 18px;

            margin-bottom: 6px;

            color: #f4f7ff !important;
            text-decoration: none !important;

            border-radius: 14px;

            font-size: 15px;
            font-weight: 600;

            background: transparent;

            border: 1px solid transparent;

            transition:
                background .2s ease,
                border-color .2s ease,
                transform .2s ease;
        }

        .superadmin-nav-link:hover {
            color: #ffffff !important;
            background: rgba(37, 99, 235, 0.14);

            border-color: rgba(59, 130, 246, 0.25);

            transform: translateX(2px);
        }

        .superadmin-nav-link.active {
            color: #ffffff !important;

            background: linear-gradient(135deg,
                    rgba(37, 99, 235, 0.32),
                    rgba(30, 64, 175, 0.24));

            border-color: rgba(59, 130, 246, 0.55);

            box-shadow:
                0 8px 20px rgba(0, 0, 0, 0.12),
                inset 0 0 20px rgba(59, 130, 246, 0.04);
        }

        .superadmin-nav-icon {
            width: 24px;
            min-width: 24px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 17px;

            color: #dbeafe;
        }

        .superadmin-nav-link.active .superadmin-nav-icon {
            color: #ffffff;
        }
    </style>
</head>

<body class="super-shell">
    <div class="container-fluid super-shell">
        <div class="row gx-0">
            <div class="col-12 col-xl-2">
                <aside class="super-sidebar">
                    <div class="super-brand">
                        <div>
                            <h4 class="super-brand-title mb-1">MuniResQ</h4>
                            <p class="super-brand-subtitle mb-0">Super Admin</p>
                        </div>
                    </div>
                    <div class="super-nav-title">Command Navigation</div>
                    <nav class="nav flex-column mb-4">
                        <a href="{{ route('superadmin.dashboard') }}" class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a href="{{ route('superadmin.drivers') }}" class="nav-link {{ request()->routeIs('superadmin.drivers') ? 'active' : '' }}">
                            <i class="bi bi-people"></i> Drivers
                        </a>
                        <a href="{{ route('admins.index') }}"
                            class="superadmin-nav-link {{ request()->routeIs('admins.*') ? 'active' : '' }}">

                            <span class="superadmin-nav-icon">
                                <i class="bi bi-person-plus"></i>
                            </span>

                            <span>Create Admin</span>

                        </a>
                        <a href="{{ route('superadmin.ambulances.index') }}" class="nav-link {{ request()->routeIs('superadmin.ambulances.*') ? 'active' : '' }}">
                            <i class="bi bi-truck"></i> Ambulances
                        </a>
                        <a href="{{ route('backups.index') }}" class="nav-link {{ request()->routeIs('backups.*') ? 'active' : '' }}">
                            <i class="bi bi-cloud-arrow-up"></i> Backup & Restore
                        </a>
                    </nav>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3 px-3">
                        @csrf
                        <button type="submit" class="btn btn-outline-light w-100 min-touch-target">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </aside>
            </div>
            <div class="col-12 col-xl-10">
                <main class="main-content">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>