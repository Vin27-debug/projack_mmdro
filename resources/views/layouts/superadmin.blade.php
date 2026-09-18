<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MuniResQ Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --mr-bg: #071a38;
            --mr-sidebar: #061633;
            --mr-surface: #0b2043;
            --mr-border: rgba(255, 255, 255, 0.1);
            --mr-text: #eef4ff;
            --mr-muted: rgba(255, 255, 255, 0.62);
            --mr-accent: #014cfd;
            --mr-accent-dark: #003399;
        }

        .super-shell {
            min-height: 100vh;
            background: var(--mr-bg);
            color: var(--mr-text);
        }

        .super-layout {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            min-height: 100vh;
            padding: 1.5rem 1.75rem 2.5rem;
        }

        .super-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 1040;
            width: 68px;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            overflow-y: auto;
            background: var(--mr-sidebar);
            border-right: 1px solid var(--mr-border);
            transition: width 160ms ease;
        }

        .super-sidebar:hover,
        .super-sidebar:focus-within,
        body.super-sidebar-expanded .super-sidebar {
            width: 240px;
        }

        .super-content {
            width: 100%;
            min-width: 0;
            margin-left: 68px;
            transition: margin-left 160ms ease;
        }

        body.super-sidebar-expanded .super-content {
            margin-left: 240px;
        }

        .super-brand-title,
        .super-brand-subtitle,
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
            gap: 0.7rem;
            min-height: 64px;
            padding: 0.75rem;
            border-bottom: 1px solid var(--mr-border);
            white-space: nowrap;
        }

        .super-brand-mark {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            display: grid;
            place-items: center;
            padding: 0.3rem;
            background: #0b2c68;
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .super-brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .super-brand-copy,
        .super-sidebar .nav-label,
        .super-sidebar .super-nav-group summary span,
        .super-sidebar .control-label,
        .super-sidebar .super-user-copy {
            opacity: 0;
            visibility: hidden;
            transition: opacity 100ms ease;
        }

        .super-sidebar:hover .super-brand-copy,
        .super-sidebar:focus-within .super-brand-copy,
        body.super-sidebar-expanded .super-brand-copy,
        .super-sidebar:hover .nav-label,
        .super-sidebar:focus-within .nav-label,
        body.super-sidebar-expanded .nav-label,
        .super-sidebar:hover .super-nav-group summary span,
        .super-sidebar:focus-within .super-nav-group summary span,
        body.super-sidebar-expanded .super-nav-group summary span,
        .super-sidebar:hover .control-label,
        .super-sidebar:focus-within .control-label,
        body.super-sidebar-expanded .control-label,
        .super-sidebar:hover .super-user-copy,
        .super-sidebar:focus-within .super-user-copy,
        body.super-sidebar-expanded .super-user-copy {
            opacity: 1;
            visibility: visible;
        }

        .super-brand-title {
            margin: 0;
            font-size: 0.98rem;
            font-weight: 600;
        }

        .super-brand-subtitle {
            margin: 0;
            font-size: 0.68rem;
            color: var(--mr-muted);
        }

        .super-nav {
            flex: 1;
            padding: 0.65rem 0.5rem;
        }

        .super-nav-group {
            margin: 0 0 0.55rem;
        }

        .super-nav-group summary {
            display: flex;
            align-items: center;
            height: 24px;
            padding: 0 0.6rem;
            overflow: hidden;
            color: rgba(255, 255, 255, 0.42);
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.07em;
            line-height: 1;
            text-transform: uppercase;
            white-space: nowrap;
            list-style: none;
        }

        .super-nav-group summary::-webkit-details-marker {
            display: none;
        }

        .super-nav-group summary::after {
            content: '\F282';
            margin-left: auto;
            font-family: bootstrap-icons;
            font-size: 0.65rem;
        }

        .super-sidebar:not(:hover) .super-nav-group summary::after,
        .super-sidebar:not(:focus-within) .super-nav-group summary::after,
        body:not(.super-sidebar-expanded) .super-nav-group summary::after {
            display: none;
        }

        @media (min-width: 992px) {
            .super-sidebar:not(:hover):not(:focus-within) .super-nav-group:not([open])>.nav {
                display: flex;
            }
        }

        .super-sidebar .nav-link,
        .superadmin-nav-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-height: 42px;
            width: 100%;
            padding: 0.55rem 0.6rem;
            overflow: hidden;
            border: 0;
            border-radius: 3px;
            background: transparent;
            color: rgba(255, 255, 255, 0.72) !important;
            font-size: 0.84rem;
            font-weight: 400;
            line-height: 1.2;
            text-decoration: none !important;
            white-space: nowrap;
            transition: background-color 100ms ease, color 100ms ease;
        }

        .super-sidebar .nav-link:hover,
        .superadmin-nav-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #fff !important;
        }

        .super-sidebar .nav-link.active,
        .superadmin-nav-link.active {
            background: rgba(1, 76, 253, 0.18);
            color: #fff !important;
        }

        .super-sidebar .nav-link.active::before,
        .superadmin-nav-link.active::before {
            position: absolute;
            inset: 0 auto 0 0;
            width: 3px;
            background: var(--mr-accent);
            content: '';
        }

        .super-sidebar .nav-link i,
        .superadmin-nav-icon {
            width: 28px;
            min-width: 28px;
            color: rgba(255, 255, 255, 0.68);
            font-size: 1.05rem;
            text-align: center;
        }

        .super-sidebar .nav-link.active i,
        .super-sidebar .nav-link:hover i,
        .superadmin-nav-link.active .superadmin-nav-icon,
        .superadmin-nav-link:hover .superadmin-nav-icon {
            color: #fff;
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

        .super-sidebar-footer {
            padding: 0.65rem 0.5rem 0.8rem;
            border-top: 1px solid var(--mr-border);
        }

        .super-user {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            min-height: 36px;
            padding: 0.25rem 0.6rem 0.6rem;
            white-space: nowrap;
        }

        .super-user-icon {
            width: 28px;
            min-width: 28px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
            text-align: center;
        }

        .super-user-name {
            color: #fff;
            font-size: 0.78rem;
            font-weight: 500;
        }

        .super-user-role {
            color: var(--mr-muted);
            font-size: 0.68rem;
        }

        .super-control,
        .super-logout {
            width: 100%;
            min-height: 40px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.6rem;
            border: 0;
            border-radius: 3px;
            background: transparent;
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.82rem;
            text-align: left;
            text-decoration: none;
        }

        .super-control:hover,
        .super-logout:hover,
        .super-control.active {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
        }

        .super-control.active {
            background: rgba(1, 76, 253, 0.18);
        }

        .super-control i,
        .super-logout i {
            width: 28px;
            min-width: 28px;
            font-size: 1rem;
            text-align: center;
        }

        .super-mobile-toggle,
        .super-mobile-close,
        .super-sidebar-backdrop {
            display: none;
        }

        @media (max-width: 991.98px) {
            .super-sidebar {
                width: 240px;
                transform: translateX(-100%);
                transition: transform 160ms ease;
            }

            body.super-mobile-open .super-sidebar {
                transform: translateX(0);
            }

            .super-sidebar .super-brand-copy,
            .super-sidebar .nav-label,
            .super-sidebar .super-nav-group summary span,
            .super-sidebar .control-label,
            .super-sidebar .super-user-copy {
                opacity: 1;
                visibility: visible;
            }

            .super-sidebar .super-nav-group summary::after {
                display: block;
            }

            .super-content,
            body.super-sidebar-expanded .super-content {
                margin-left: 0;
            }

            .super-mobile-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                margin-bottom: 1rem;
                border: 1px solid var(--mr-border);
                border-radius: 3px;
                background: var(--mr-surface);
                color: #fff;
            }

            .super-mobile-close {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 32px;
                height: 32px;
                margin-left: auto;
                border: 0;
                background: transparent;
                color: rgba(255, 255, 255, 0.75);
                font-size: 1.15rem;
            }

            .super-sidebar-backdrop {
                position: fixed;
                inset: 0;
                z-index: 1030;
                display: block;
                background: rgba(0, 0, 0, 0.48);
                opacity: 0;
                pointer-events: none;
                transition: opacity 160ms ease;
            }

            body.super-mobile-open .super-sidebar-backdrop {
                opacity: 1;
                pointer-events: auto;
            }

            .main-content {
                padding: 1rem 1.25rem 2rem;
            }
        }
    </style>
</head>

<body class="super-shell">
    <div class="super-layout">
        <aside class="super-sidebar" id="superSidebar" aria-label="Super admin sidebar">
            <div class="super-brand">
                <div class="super-brand-mark">
                    <img src="{{ asset('favicon.ico') }}" alt="MuniResQ logo">
                </div>
                <div class="super-brand-copy">
                    <h1 class="super-brand-title">MuniResQ</h1>
                    <p class="super-brand-subtitle">MDRRMO Management System</p>
                </div>
                <button type="button" class="super-mobile-close" data-super-sidebar-close aria-label="Close navigation">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <nav class="super-nav" aria-label="Super admin navigation">
                <details class="super-nav-group" open>
                    <summary><span>Operations</span></summary>
                    <nav class="nav flex-column">
                        <a href="{{ route('superadmin.dashboard') }}" class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i><span class="nav-label">Dashboard</span></a>
                    </nav>
                </details>
                <details class="super-nav-group" open>
                    <summary><span>Fleet</span></summary>
                    <nav class="nav flex-column">
                        <a href="{{ route('superadmin.ambulances.index') }}" class="nav-link {{ request()->routeIs('superadmin.ambulances.*') ? 'active' : '' }}"><i class="bi bi-truck"></i><span class="nav-label">Ambulances / Vehicles</span></a>
                        <a href="{{ route('assignments.index') }}" class="nav-link {{ request()->routeIs('assignments.*') ? 'active' : '' }}"><i class="bi bi-arrows-move"></i><span class="nav-label">Assignments</span></a>
                    </nav>
                </details>
                <details class="super-nav-group" open>
                    <summary><span>User Management</span></summary>
                    <nav class="nav flex-column">
                        <a href="{{ route('superadmin.users.pending') }}" class="nav-link {{ request()->routeIs('superadmin.users.pending') ? 'active' : '' }}"><i class="bi bi-person-check"></i><span class="nav-label">Pending Users</span></a>
                        <a href="{{ route('superadmin.drivers') }}" class="nav-link {{ request()->routeIs('superadmin.drivers*') ? 'active' : '' }}"><i class="bi bi-person-badge"></i><span class="nav-label">Drivers</span></a>
                        <a href="{{ route('admins.index') }}" class="superadmin-nav-link {{ request()->routeIs('admins.*') ? 'active' : '' }}"><i class="bi bi-person-plus superadmin-nav-icon"></i><span class="nav-label">Admins</span></a>
                    </nav>
                </details>
                <details class="super-nav-group" {{ request()->routeIs('superadmin.settings') || request()->routeIs('backups.*') ? 'open' : '' }}>
                    <summary><span>System</span></summary>
                    <nav class="nav flex-column">
                        <a href="{{ route('superadmin.settings') }}" class="nav-link {{ request()->routeIs('superadmin.settings') ? 'active' : '' }}"><i class="bi bi-sliders"></i><span class="nav-label">Settings</span></a>
                        <a href="{{ route('backups.index') }}" class="nav-link {{ request()->routeIs('backups.*') ? 'active' : '' }}"><i class="bi bi-database-up"></i><span class="nav-label">Backup &amp; Restore</span></a>
                    </nav>
                </details>
            </nav>

            <div class="super-sidebar-footer">
                <div class="super-user">
                    <i class="bi bi-person-circle super-user-icon"></i>
                    <div class="super-user-copy">
                        <div class="super-user-name">{{ auth()->user()->name ?? 'Administrator' }}</div>
                        <div class="super-user-role">Super Administrator</div>
                    </div>
                </div>
                <button type="button" class="super-control" data-bs-toggle="modal" data-bs-target="#superAdminHelpModal"><i class="bi bi-question-circle"></i><span class="control-label">Help</span></button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="super-logout"><i class="bi bi-box-arrow-right"></i><span class="control-label">Logout</span></button>
                </form>
            </div>
        </aside>

        <div class="super-sidebar-backdrop" data-super-sidebar-close></div>

        <div class="super-content">
            <main class="main-content">
                <button type="button" class="super-mobile-toggle" data-super-sidebar-open aria-label="Open navigation"><i class="bi bi-list"></i></button>
                @yield('content')
            </main>
        </div>
    </div>

    <div class="modal fade" id="superAdminHelpModal" tabindex="-1" aria-labelledby="superAdminHelpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="superAdminHelpModalLabel">System Administration Help</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="mb-0 ps-3">
                        <li>Use Drivers and Ambulances to keep fleet readiness current.</li>
                        <li>Review backup and restore actions before making any destructive recovery decision.</li>
                        <li>Create or manage admin accounts from the command navigation.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const shell = document.body;
            const openButton = document.querySelector('[data-super-sidebar-open]');
            const closeButtons = document.querySelectorAll('[data-super-sidebar-close]');

            const closeMobileSidebar = () => shell.classList.remove('super-mobile-open');
            const openMobileSidebar = () => shell.classList.add('super-mobile-open');

            openButton?.addEventListener('click', openMobileSidebar);
            closeButtons.forEach(button => button.addEventListener('click', closeMobileSidebar));
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape') {
                    closeMobileSidebar();
                }
            });
        });
    </script>
</body>

</html>