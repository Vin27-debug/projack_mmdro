@extends('layouts.superadmin')

@section('content')

<div class="sa-dashboard">

    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div class="sa-header">

        <div>
            <div class="sa-eyebrow">
                SUPER ADMINISTRATION
            </div>

            <h1>
                Command Center
            </h1>

            <p>
                Manage the MuniResQ system, administrators, fleet,
                and emergency operations.
            </p>
        </div>

        <div class="sa-header-right">

            <div class="sa-system-status">
                <span></span>
                System Operational
            </div>

            <div class="sa-date">
                {{ now()->format('l, F j, Y') }}
            </div>

            <div class="sa-time">
                {{ now()->format('H:i') }}
            </div>

        </div>

    </div>


    {{-- =====================================================
         QUICK ACTIONS
    ====================================================== --}}

    <div class="sa-section-heading">

        <div>
            <h2>System Management</h2>
            <p>Quick access to administrative controls.</p>
        </div>

    </div>


    <div class="sa-management-grid">

        {{-- CREATE ADMIN --}}

        <a
            href="{{ route('admins.index') }}"
            class="sa-management-card primary">

            <div class="sa-management-icon">
                <i class="bi bi-person-plus"></i>
            </div>

            <div class="sa-management-content">

                <h3>Create Admin</h3>

                <p>
                    Add a new administrator to the MuniResQ system.
                </p>

                <span>
                    Create account
                    <i class="bi bi-arrow-right"></i>
                </span>

            </div>

        </a>


        {{-- DRIVERS --}}

        <a
            href="{{ route('superadmin.drivers') }}"
            class="sa-management-card">

            <div class="sa-management-icon">
                <i class="bi bi-person-badge"></i>
            </div>

            <div class="sa-management-content">

                <h3>Driver Management</h3>

                <p>
                    Review drivers and manage their assignments.
                </p>

                <span>
                    Manage drivers
                    <i class="bi bi-arrow-right"></i>
                </span>

            </div>

        </a>


        {{-- AMBULANCES --}}

        <a
            href="{{ route('superadmin.ambulances.index') }}"
            class="sa-management-card">

            <div class="sa-management-icon">
                <i class="bi bi-truck-front"></i>
            </div>

            <div class="sa-management-content">

                <h3>Ambulances</h3>

                <p>
                    Manage ambulance records and fleet assignments.
                </p>

                <span>
                    Manage fleet
                    <i class="bi bi-arrow-right"></i>
                </span>

            </div>

        </a>


        {{-- PENDING USERS --}}

        <a
            href="{{ route('superadmin.users.pending') }}"
            class="sa-management-card">

            <div class="sa-management-icon">
                <i class="bi bi-person-check"></i>
            </div>

            <div class="sa-management-content">

                <h3>Pending Accounts</h3>

                <p>
                    Review users waiting for account approval.
                </p>

                <span>
                    Review accounts
                    <i class="bi bi-arrow-right"></i>
                </span>

            </div>

        </a>

    </div>


    {{-- =====================================================
         STATISTICS
    ====================================================== --}}

    <div class="sa-section-heading sa-stats-heading">

        <div>
            <h2>System Overview</h2>
            <p>Current operational statistics.</p>
        </div>

    </div>


    <div class="sa-stat-grid">

        <div class="sa-stat-card">

            <div class="sa-stat-top">
                <span>Total Incidents</span>

                <i class="bi bi-exclamation-triangle"></i>
            </div>

            <strong>
                {{ $stats['total_incidents'] }}
            </strong>

            <small>
                All recorded incidents
            </small>

        </div>


        <div class="sa-stat-card">

            <div class="sa-stat-top">
                <span>Pending</span>

                <i class="bi bi-hourglass-split"></i>
            </div>

            <strong>
                {{ $stats['pending_incidents'] }}
            </strong>

            <small>
                Awaiting response
            </small>

        </div>


        <div class="sa-stat-card">

            <div class="sa-stat-top">
                <span>Dispatched</span>

                <i class="bi bi-send"></i>
            </div>

            <strong>
                {{ $stats['dispatched_incidents'] }}
            </strong>

            <small>
                Currently dispatched
            </small>

        </div>


        <div class="sa-stat-card">

            <div class="sa-stat-top">
                <span>Completed</span>

                <i class="bi bi-check-circle"></i>
            </div>

            <strong>
                {{ $stats['completed_incidents'] }}
            </strong>

            <small>
                Successfully completed
            </small>

        </div>


        <div class="sa-stat-card">

            <div class="sa-stat-top">
                <span>Total Drivers</span>

                <i class="bi bi-people"></i>
            </div>

            <strong>
                {{ $stats['total_drivers'] }}
            </strong>

            <small>
                Registered drivers
            </small>

        </div>


        <div class="sa-stat-card">

            <div class="sa-stat-top">
                <span>Available Ambulances</span>

                <i class="bi bi-truck"></i>
            </div>

            <strong>
                {{ $stats['available_ambulances'] }}
            </strong>

            <small>
                Ready for deployment
            </small>

        </div>

    </div>


    {{-- =====================================================
         PANIC ALERTS
    ====================================================== --}}

    @if(isset($activePanicAlerts) && $activePanicAlerts->count())

    <div class="sa-alert-section">

        <div class="sa-alert-header">

            <div class="sa-alert-title">

                <div class="sa-alert-icon">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                </div>

                <div>
                    <h2>Active Emergency Alerts</h2>
                    <p>
                        Immediate attention required.
                    </p>
                </div>

            </div>

            <span class="sa-critical">
                {{ $activePanicAlerts->count() }} ACTIVE
            </span>

        </div>


        <div class="sa-alert-list">

            @foreach($activePanicAlerts as $alert)

            <div class="sa-alert-item">

                <div>

                    <strong>
                        {{ $alert->driver->user->name ?? 'Unknown Driver' }}
                    </strong>

                    <p>
                        Panic alert triggered
                    </p>

                </div>

                <div class="sa-alert-location">

                    <span>
                        <i class="bi bi-geo-alt"></i>
                        {{ $alert->latitude }},
                        {{ $alert->longitude }}
                    </span>

                    <small>
                        {{ $alert->triggered_at }}
                    </small>

                </div>

            </div>

            @endforeach

        </div>

    </div>

    @endif


    {{-- =====================================================
         RECENT INCIDENTS
    ====================================================== --}}

    <div class="sa-section-heading sa-recent-heading">

        <div>
            <h2>Recent Incidents</h2>
            <p>
                Latest emergency activity recorded in MuniResQ.
            </p>
        </div>

        <a href="{{ route('admin.incidents.index') }}">
            View all
            <i class="bi bi-arrow-right"></i>
        </a>

    </div>


    <div class="sa-table-card">

        <div class="sa-table-wrapper">

            <table>

                <thead>

                    <tr>
                        <th>Incident</th>
                        <th>Reporter</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Status</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($recentIncidents as $incident)

                    <tr>

                        <td>
                            <strong>
                                {{ $incident->incident_number }}
                            </strong>
                        </td>

                        <td>
                            {{ $incident->reporter_name }}
                        </td>

                        <td>
                            {{ $incident->incident_type }}
                        </td>

                        <td>
                            <span class="sa-location">
                                <i class="bi bi-geo-alt"></i>
                                {{ $incident->location }}
                            </span>
                        </td>

                        <td>

                            <span class="sa-status">
                                {{ strtoupper($incident->status) }}
                            </span>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="5">

                            <div class="sa-empty">

                                <i class="bi bi-inbox"></i>

                                <strong>
                                    No incidents recorded
                                </strong>

                                <p>
                                    New incidents will appear here.
                                </p>

                            </div>

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


<style>
    /* =========================================================
   SUPER ADMIN DASHBOARD
========================================================= */

    .sa-dashboard {
        width: 100%;
        max-width: 1500px;
        margin: 0 auto;
        padding: 8px 4px 40px;
    }


    /* =========================================================
   HEADER
========================================================= */

    .sa-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;

        padding: 30px;

        margin-bottom: 30px;

        border-radius: 22px;

        background:
            linear-gradient(135deg,
                rgba(20, 42, 85, .95),
                rgba(8, 21, 45, .95));

        border: 1px solid rgba(255, 255, 255, .09);

        box-shadow:
            0 20px 50px rgba(0, 0, 0, .18);
    }

    .sa-eyebrow {
        margin-bottom: 8px;

        color: #6f91ff;

        font-size: .7rem;
        font-weight: 700;

        letter-spacing: .16em;
    }

    .sa-header h1 {
        margin: 0;

        color: #fff;

        font-size: 2rem;
        font-weight: 750;

        letter-spacing: -.04em;
    }

    .sa-header p {
        margin: 8px 0 0;

        color: rgba(255, 255, 255, .58);

        font-size: .9rem;
    }

    .sa-header-right {
        text-align: right;
    }

    .sa-system-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        padding: 7px 11px;

        border-radius: 30px;

        background: rgba(39, 185, 112, .1);

        color: #72dfa7;

        font-size: .75rem;
        font-weight: 600;
    }

    .sa-system-status span {
        width: 7px;
        height: 7px;

        border-radius: 50%;

        background: #43d98d;
    }

    .sa-date {
        margin-top: 13px;

        color: rgba(255, 255, 255, .45);

        font-size: .75rem;
    }

    .sa-time {
        margin-top: 2px;

        color: #fff;

        font-size: 1.25rem;
        font-weight: 700;
    }


    /* =========================================================
   SECTION HEADINGS
========================================================= */

    .sa-section-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;

        margin-bottom: 14px;
    }

    .sa-section-heading h2 {
        margin: 0;

        color: #fff;

        font-size: 1.05rem;
        font-weight: 700;
    }

    .sa-section-heading p {
        margin: 4px 0 0;

        color: rgba(255, 255, 255, .48);

        font-size: .78rem;
    }


    /* =========================================================
   MANAGEMENT
========================================================= */

    .sa-management-grid {
        display: grid;

        grid-template-columns:
            repeat(4, minmax(0, 1fr));

        gap: 14px;

        margin-bottom: 30px;
    }

    .sa-management-card {
        display: flex;

        min-height: 155px;

        padding: 20px;

        gap: 15px;

        text-decoration: none;

        border-radius: 18px;

        background: rgba(8, 22, 46, .82);

        border: 1px solid rgba(255, 255, 255, .08);

        transition:
            transform .18s ease,
            border-color .18s ease,
            background .18s ease;
    }

    .sa-management-card:hover {
        transform: translateY(-3px);

        background: rgba(15, 34, 68, .95);

        border-color: rgba(111, 145, 255, .4);
    }

    .sa-management-card.primary {
        background:
            linear-gradient(135deg,
                rgba(59, 105, 255, .22),
                rgba(20, 42, 85, .72));

        border-color: rgba(78, 119, 255, .32);
    }

    .sa-management-icon {
        width: 43px;
        height: 43px;

        flex-shrink: 0;

        display: grid;
        place-items: center;

        border-radius: 12px;

        background: rgba(255, 255, 255, .07);

        color: #8da6ff;

        font-size: 1.15rem;
    }

    .sa-management-content {
        min-width: 0;
    }

    .sa-management-content h3 {
        margin: 2px 0 6px;

        color: #fff;

        font-size: .92rem;
        font-weight: 650;
    }

    .sa-management-content p {
        margin: 0;

        color: rgba(255, 255, 255, .5);

        font-size: .76rem;

        line-height: 1.5;
    }

    .sa-management-content span {
        display: inline-flex;

        align-items: center;

        gap: 6px;

        margin-top: 13px;

        color: #8da6ff;

        font-size: .72rem;
        font-weight: 600;
    }


    /* =========================================================
   STATISTICS
========================================================= */

    .sa-stats-heading {
        margin-top: 4px;
    }

    .sa-stat-grid {
        display: grid;

        grid-template-columns:
            repeat(6, minmax(0, 1fr));

        gap: 12px;

        margin-bottom: 30px;
    }

    .sa-stat-card {
        padding: 17px;

        min-height: 125px;

        border-radius: 16px;

        background: rgba(8, 22, 46, .75);

        border: 1px solid rgba(255, 255, 255, .07);
    }

    .sa-stat-top {
        display: flex;

        justify-content: space-between;

        align-items: center;

        color: rgba(255, 255, 255, .48);

        font-size: .72rem;
    }

    .sa-stat-top i {
        color: #708dff;

        font-size: 1rem;
    }

    .sa-stat-card strong {
        display: block;

        margin-top: 13px;

        color: #fff;

        font-size: 1.65rem;
        line-height: 1;
    }

    .sa-stat-card small {
        display: block;

        margin-top: 8px;

        color: rgba(255, 255, 255, .35);

        font-size: .68rem;
    }


    /* =========================================================
   ALERTS
========================================================= */

    .sa-alert-section {
        margin-bottom: 30px;

        border-radius: 18px;

        background: rgba(75, 17, 28, .22);

        border: 1px solid rgba(255, 76, 96, .22);

        overflow: hidden;
    }

    .sa-alert-header {
        display: flex;

        justify-content: space-between;

        align-items: center;

        padding: 17px 20px;

        border-bottom: 1px solid rgba(255, 255, 255, .06);
    }

    .sa-alert-title {
        display: flex;

        align-items: center;

        gap: 12px;
    }

    .sa-alert-icon {
        width: 38px;
        height: 38px;

        display: grid;
        place-items: center;

        border-radius: 10px;

        background: rgba(220, 53, 69, .15);

        color: #ff7887;
    }

    .sa-alert-title h2 {
        margin: 0;

        color: #fff;

        font-size: .92rem;
    }

    .sa-alert-title p {
        margin: 3px 0 0;

        color: rgba(255, 255, 255, .45);

        font-size: .72rem;
    }

    .sa-critical {
        padding: 6px 9px;

        border-radius: 20px;

        background: rgba(220, 53, 69, .15);

        color: #ff8793;

        font-size: .65rem;
        font-weight: 700;
    }

    .sa-alert-list {
        padding: 0 20px;
    }

    .sa-alert-item {
        display: flex;

        align-items: center;

        justify-content: space-between;

        padding: 14px 0;

        border-bottom: 1px solid rgba(255, 255, 255, .06);
    }

    .sa-alert-item:last-child {
        border-bottom: none;
    }

    .sa-alert-item strong {
        color: #fff;

        font-size: .8rem;
    }

    .sa-alert-item p {
        margin: 3px 0 0;

        color: rgba(255, 255, 255, .42);

        font-size: .7rem;
    }

    .sa-alert-location {
        text-align: right;
    }

    .sa-alert-location span {
        display: block;

        color: rgba(255, 255, 255, .55);

        font-size: .7rem;
    }

    .sa-alert-location i {
        color: #ff7181;
    }

    .sa-alert-location small {
        display: block;

        margin-top: 4px;

        color: rgba(255, 255, 255, .32);

        font-size: .65rem;
    }


    /* =========================================================
   RECENT
========================================================= */

    .sa-recent-heading {
        margin-bottom: 12px;
    }

    .sa-recent-heading>a {
        display: inline-flex;

        align-items: center;

        gap: 6px;

        color: #7e9aff;

        font-size: .75rem;

        text-decoration: none;
    }

    .sa-table-card {
        overflow: hidden;

        border-radius: 18px;

        background: rgba(8, 22, 46, .78);

        border: 1px solid rgba(255, 255, 255, .07);
    }

    .sa-table-wrapper {
        overflow-x: auto;
    }

    .sa-table-card table {
        width: 100%;

        border-collapse: collapse;
    }

    .sa-table-card th {
        padding: 13px 17px;

        background: rgba(255, 255, 255, .025);

        border-bottom: 1px solid rgba(255, 255, 255, .07);

        color: rgba(255, 255, 255, .4);

        font-size: .67rem;
        font-weight: 650;

        text-align: left;

        text-transform: uppercase;

        letter-spacing: .06em;
    }

    .sa-table-card td {
        padding: 15px 17px;

        border-bottom: 1px solid rgba(255, 255, 255, .05);

        color: rgba(255, 255, 255, .65);

        font-size: .76rem;
    }

    .sa-table-card tbody tr {
        transition: background .15s ease;
    }

    .sa-table-card tbody tr:hover {
        background: rgba(59, 105, 255, .05);
    }

    .sa-table-card tbody tr:last-child td {
        border-bottom: none;
    }

    .sa-table-card td strong {
        color: #fff;
        font-weight: 600;
    }

    .sa-location {
        display: inline-flex;

        align-items: center;

        gap: 5px;
    }

    .sa-location i {
        color: #728eff;
    }

    .sa-status {
        display: inline-block;

        padding: 5px 8px;

        border-radius: 6px;

        background: rgba(255, 255, 255, .07);

        color: rgba(255, 255, 255, .65);

        font-size: .62rem;
        font-weight: 650;
    }

    .sa-empty {
        padding: 45px 15px;

        text-align: center;
    }

    .sa-empty i {
        display: block;

        margin-bottom: 8px;

        color: rgba(255, 255, 255, .2);

        font-size: 2rem;
    }

    .sa-empty strong {
        display: block;

        color: rgba(255, 255, 255, .65);

        font-size: .82rem;
    }

    .sa-empty p {
        margin: 5px 0 0;

        color: rgba(255, 255, 255, .35);

        font-size: .7rem;
    }


    /* =========================================================
   RESPONSIVE
========================================================= */

    @media (max-width: 1200px) {

        .sa-management-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .sa-stat-grid {
            grid-template-columns: repeat(3, 1fr);
        }

    }

    @media (max-width: 768px) {

        .sa-header {
            flex-direction: column;
            gap: 20px;
        }

        .sa-header-right {
            text-align: left;
        }

        .sa-management-grid {
            grid-template-columns: 1fr;
        }

        .sa-stat-grid {
            grid-template-columns: repeat(2, 1fr);
        }

    }

    @media (max-width: 500px) {

        .sa-dashboard {
            padding-left: 0;
            padding-right: 0;
        }

        .sa-header {
            padding: 22px;
        }

        .sa-stat-grid {
            grid-template-columns: 1fr;
        }

    }
</style>

@endsection