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
            --mr-bg: #071a38;
            --mr-sidebar: #061633;
            --mr-surface: #0b2043;
            --mr-border: rgba(255, 255, 255, 0.1);
            --mr-text: #eef4ff;
            --mr-muted: rgba(255, 255, 255, 0.62);
            --mr-accent: #014cfd;
            --mr-accent-dark: #003399;
            --mr-danger: #dc3545;
            --mr-success: #1c8e5b;
            --mr-warning: #f8b620;
        }

        body {
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
            font-family: "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI Emoji", sans-serif;
            background: var(--mr-bg);
            color: var(--mr-text);
        }

        .admin-shell {
            min-height: 100vh;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
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

        .admin-sidebar:hover,
        .admin-sidebar:focus-within,
        body.admin-sidebar-expanded .admin-sidebar {
            width: 240px;
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            min-height: 64px;
            padding: 0.75rem 0.75rem;
            border-bottom: 1px solid var(--mr-border);
            white-space: nowrap;
        }

        .admin-brand-mark {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            display: grid;
            place-items: center;
            padding: 0.3rem;
            background: #0b2c68;
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .admin-brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .admin-brand-copy,
        .admin-sidebar .nav-label,
        .admin-sidebar .admin-nav-group summary span,
        .admin-sidebar .control-label,
        .admin-sidebar .admin-user-copy {
            display: none;
        }

        .admin-sidebar:hover .admin-brand-copy,
        .admin-sidebar:focus-within .admin-brand-copy,
        body.admin-sidebar-expanded .admin-brand-copy,
        .admin-sidebar:hover .nav-label,
        .admin-sidebar:focus-within .nav-label,
        body.admin-sidebar-expanded .nav-label,
        .admin-sidebar:hover .admin-nav-group summary span,
        .admin-sidebar:focus-within .admin-nav-group summary span,
        body.admin-sidebar-expanded .admin-nav-group summary span,
        .admin-sidebar:hover .control-label,
        .admin-sidebar:focus-within .control-label,
        body.admin-sidebar-expanded .control-label,
        .admin-sidebar:hover .admin-user-copy,
        .admin-sidebar:focus-within .admin-user-copy,
        body.admin-sidebar-expanded .admin-user-copy {
            display: block;
        }

        @media (min-width: 992px) {
            .admin-sidebar:not(:hover):not(:focus-within) .admin-brand {
                justify-content: center;
            }

            .admin-sidebar:not(:hover):not(:focus-within) .admin-nav-group summary {
                display: none;
            }

            .admin-sidebar:not(:hover):not(:focus-within) .nav-link,
            .admin-sidebar:not(:hover):not(:focus-within) .admin-control,
            .admin-sidebar:not(:hover):not(:focus-within) .admin-logout,
            .admin-sidebar:not(:hover):not(:focus-within) .admin-user {
                justify-content: center;
            }

            .admin-sidebar:not(:hover):not(:focus-within) .nav-link,
            .admin-sidebar:not(:hover):not(:focus-within) .admin-control,
            .admin-sidebar:not(:hover):not(:focus-within) .admin-logout {
                padding-left: 0.6rem;
                padding-right: 0.6rem;
            }

            .admin-sidebar:not(:hover):not(:focus-within) .admin-sidebar-footer .admin-user {
                padding-left: 0;
                padding-right: 0;
            }

            .admin-sidebar:not(:hover):not(:focus-within) .admin-sidebar .badge {
                display: none;
            }

            .admin-sidebar:hover .admin-nav-group summary,
            .admin-sidebar:focus-within .admin-nav-group summary,
            body.admin-sidebar-expanded .admin-nav-group summary {
                display: flex;
            }
        }

        .admin-brand-title {
            margin: 0;
            color: #fff;
            font-size: 0.98rem;
            font-weight: 600;
        }

        .admin-brand-subtitle {
            margin: 0;
            color: var(--mr-muted);
            font-size: 0.68rem;
        }

        .admin-nav {
            flex: 1;
            padding: 0.65rem 0.5rem;
        }

        .admin-nav-group {
            margin: 0 0 0.55rem;
        }

        .admin-nav-group summary {
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

        .admin-nav-group summary::-webkit-details-marker {
            display: none;
        }

        .admin-nav-group summary::after {
            content: '\F282';
            margin-left: auto;
            font-family: bootstrap-icons;
            font-size: 0.65rem;
        }

        .admin-sidebar:not(:hover) .admin-nav-group summary::after,
        .admin-sidebar:not(:focus-within) .admin-nav-group summary::after,
        body:not(.admin-sidebar-expanded) .admin-nav-group summary::after {
            display: none;
        }

        .admin-nav-group .nav {
            gap: 2px;
        }

        @media (min-width: 992px) {
            .admin-sidebar:not(:hover):not(:focus-within) .admin-nav-group:not([open])>.nav {
                display: flex;
            }
        }

        .admin-sidebar .nav-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-height: 42px;
            padding: 0.55rem 0.6rem;
            overflow: hidden;
            border-radius: 3px;
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.84rem;
            font-weight: 400;
            line-height: 1.2;
            text-decoration: none;
            white-space: nowrap;
            transition: background-color 100ms ease, color 100ms ease;
        }

        .admin-sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
        }

        .admin-sidebar .nav-link.active {
            background: rgba(1, 76, 253, 0.18);
            color: #fff;
        }

        .admin-sidebar .nav-link.active::before {
            position: absolute;
            inset: 0 auto 0 0;
            width: 3px;
            background: var(--mr-accent);
            content: '';
        }

        .admin-sidebar .nav-link i {
            width: 28px;
            min-width: 28px;
            color: rgba(255, 255, 255, 0.68);
            font-size: 1.05rem;
            text-align: center;
        }

        @media (min-width: 992px) {

            .admin-sidebar:not(:hover):not(:focus-within) .admin-nav-group summary span,
            .admin-sidebar:not(:hover):not(:focus-within) .nav-label,
            .admin-sidebar:not(:hover):not(:focus-within) .admin-brand-copy,
            .admin-sidebar:not(:hover):not(:focus-within) .admin-user-copy,
            .admin-sidebar:not(:hover):not(:focus-within) .control-label {
                display: none;
            }
        }

        .admin-sidebar .nav-link.active i,
        .admin-sidebar .nav-link:hover i {
            color: #fff;
        }

        .admin-sidebar .badge {
            margin-left: auto;
            min-width: 1.4rem;
            padding: 0.22rem 0.35rem;
            border-radius: 2px;
            font-size: 0.68rem;
        }

        .admin-sidebar-footer {
            padding: 0.65rem 0.5rem 0.8rem;
            border-top: 1px solid var(--mr-border);
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            min-height: 36px;
            padding: 0.25rem 0.6rem 0.6rem;
            white-space: nowrap;
        }

        .admin-user-icon {
            width: 28px;
            min-width: 28px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
            text-align: center;
        }

        .admin-user-name {
            color: #fff;
            font-size: 0.78rem;
            font-weight: 500;
        }

        .admin-user-role {
            color: var(--mr-muted);
            font-size: 0.68rem;
        }

        .admin-control,
        .admin-logout {
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
        }

        .admin-control:hover,
        .admin-logout:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
        }

        .admin-control.active {
            background: rgba(1, 76, 253, 0.18);
            color: #fff;
        }

        .admin-control i,
        .admin-logout i {
            width: 28px;
            min-width: 28px;
            font-size: 1rem;
            text-align: center;
        }

        .admin-mobile-toggle,
        .admin-mobile-close,
        .admin-sidebar-backdrop {
            display: none;
        }

        .admin-content {
            width: calc(100% - 68px);
            min-width: 0;
            margin-left: 68px;
            transition: margin-left 160ms ease;
        }

        body.admin-sidebar-expanded .admin-content {
            width: calc(100% - 240px);
            margin-left: 240px;
        }

        @media (min-width: 992px) {

            body:has(.admin-sidebar:hover) .admin-content,
            body:has(.admin-sidebar:focus-within) .admin-content {
                width: calc(100% - 240px);
                margin-left: 240px;
            }
        }

        .main-content {
            min-height: 100vh;
            padding: 1.5rem 1.75rem 2.5rem;
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

        @media (max-width: 991.98px) {
            .admin-sidebar {
                width: 240px;
                transform: translateX(-100%);
                transition: transform 160ms ease;
            }

            body.admin-mobile-open .admin-sidebar {
                transform: translateX(0);
            }

            .admin-sidebar .admin-brand-copy,
            .admin-sidebar .nav-label,
            .admin-sidebar .admin-nav-group summary span,
            .admin-sidebar .control-label,
            .admin-sidebar .admin-user-copy {
                display: block;
            }

            .admin-sidebar .admin-nav-group summary::after {
                display: block;
            }

            .admin-content,
            body.admin-sidebar-expanded .admin-content {
                width: 100%;
                margin-left: 0;
            }

            .admin-mobile-toggle {
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

            .admin-mobile-close {
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

            .admin-sidebar-backdrop {
                position: fixed;
                inset: 0;
                z-index: 1030;
                display: block;
                background: rgba(0, 0, 0, 0.48);
                opacity: 0;
                pointer-events: none;
                transition: opacity 160ms ease;
            }

            body.admin-mobile-open .admin-sidebar-backdrop {
                opacity: 1;
                pointer-events: auto;
            }

            .main-content {
                padding: 1rem 1.25rem 2rem;
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

    <div class="admin-layout">
        <aside class="admin-sidebar" id="adminSidebar" aria-label="Admin sidebar">

    <div class="admin-brand">
        <div class="admin-brand-mark">
            <img src="{{ asset('favicon.ico') }}" alt="MuniResQ logo">
        </div>

        <div class="admin-brand-copy">
            <div class="admin-brand-title">MuniResQ</div>
            <div class="admin-brand-subtitle">Admin Command</div>
        </div>
    </div>

    <nav class="admin-nav" aria-label="Admin navigation">

        <details class="admin-nav-group" open>
            <summary><span>Operations</span></summary>

            <nav class="nav flex-column">
                <a href="{{ route('admin.dashboard') }}"
                   class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-label">Dashboard</span>
                </a>

                <a href="{{ url('/admin/incidents') }}"
                   class="nav-link {{ request()->is('admin/incidents*') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span class="nav-label">Incidents</span>
                </a>

                <a href="{{ $adminRoute('admin.dispatches.index') }}"
                   class="nav-link {{ request()->routeIs('admin.dispatches.*') ? 'active' : '' }}">
                    <i class="bi bi-broadcast-pin"></i>
                    <span class="nav-label">Dispatch Center</span>
                </a>

                <a href="{{ $adminRoute('admin.gps.monitoring') }}"
                   class="nav-link {{ request()->routeIs('admin.gps.monitoring') ? 'active' : '' }}">
                    <i class="bi bi-geo-alt"></i>
                    <span class="nav-label">GPS Monitoring</span>
                </a>

                <a href="{{ $adminRoute('admin.operations.center') }}"
                   class="nav-link {{ request()->routeIs('admin.operations.center') ? 'active' : '' }}">
                    <i class="bi bi-crosshair"></i>
                    <span class="nav-label">Operations Center</span>
                </a>
            </nav>
        </details>

        <details class="admin-nav-group" open>
            <summary><span>Communication</span></summary>

            <nav class="nav flex-column">
                <a href="{{ $adminRoute('admin.notifications.index') }}"
                   class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                    <i class="bi bi-bell"></i>
                    <span class="nav-label">Notifications</span>
                    <span class="badge bg-danger ms-auto" data-unread-badge>
                        {{ $unreadNotifications ?? 0 }}
                    </span>
                </a>
            </nav>
        </details>

        <details class="admin-nav-group" open>
            <summary><span>Fleet</span></summary>

            <nav class="nav flex-column">
                <a href="{{ $adminRoute('admin.ambulances.index') }}"
                   class="nav-link {{ request()->routeIs('admin.ambulances.*') ? 'active' : '' }}">
                    <i class="bi bi-truck-front"></i>
                    <span class="nav-label">Ambulances</span>
                </a>
            </nav>
        </details>

        <details class="admin-nav-group" open>
            <summary><span>Information</span></summary>

            <nav class="nav flex-column">
                <a href="{{ $adminRoute('admin.vulnerable-areas.index') }}"
                   class="nav-link {{ request()->routeIs('admin.vulnerable-areas.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span class="nav-label">Vulnerable Areas</span>
                </a>
            </nav>
        </details>

        <details class="admin-nav-group" open>
            <summary><span>System</span></summary>

            <nav class="nav flex-column">
                <a href="{{ route('admin.audit-logs.index') }}"
                   class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i>
                    <span class="nav-label">Audit Logs</span>
                </a>
            </nav>
        </details>

    </nav>

    <div class="admin-sidebar-footer">

        <div class="admin-user">
            <i class="bi bi-person-circle admin-user-icon"></i>

            <div class="admin-user-copy">
                <div class="admin-user-name">
                    {{ auth()->user()->name ?? 'Administrator' }}
                </div>

                <div class="admin-user-role">
                    Admin account
                </div>
            </div>
        </div>

        <button type="button"
                class="admin-control"
                data-bs-toggle="modal"
                data-bs-target="#adminHelpModal">
            <i class="bi bi-question-circle"></i>
            <span class="control-label">Help</span>
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="admin-logout">
                <i class="bi bi-box-arrow-right"></i>
                <span class="control-label">Logout</span>
            </button>
        </form>

    </div>

</aside>

        <div class="admin-sidebar-backdrop" data-admin-sidebar-close></div>

        <div class="admin-content">
            <main class="main-content">
                <button type="button" class="admin-mobile-toggle" data-admin-sidebar-open aria-label="Open navigation">
                    <i class="bi bi-list"></i>
                </button>
                @yield('content')
            </main>
        </div>
    </div>

    <div class="modal fade" id="adminHelpModal" tabindex="-1" aria-labelledby="adminHelpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="adminHelpModalLabel">Command Center Help</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="mb-0 ps-3">
                        <li>Track incidents from report to dispatch and closeout.</li>
                        <li>Use Audit Logs to review who changed emergency timestamps.</li>
                        <li>Review Notifications for live operational updates and pending actions.</li>
                        <li>Use Reports Center to monitor response times and trends across the fleet.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const shell = document.body;
            const openButton = document.querySelector('[data-admin-sidebar-open]');
            const closeButtons = document.querySelectorAll('[data-admin-sidebar-close]');

            const closeMobileSidebar = () => shell.classList.remove('admin-mobile-open');
            const openMobileSidebar = () => shell.classList.add('admin-mobile-open');

            openButton?.addEventListener('click', openMobileSidebar);
            closeButtons.forEach(button => button.addEventListener('click', closeMobileSidebar));
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape') {
                    closeMobileSidebar();
                }
            });

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


