@extends('layouts.admin')

@section('content')
<style>
    :root {
        --gov-navy: #0d214b;
        --gov-ink: #0b172f;
        --gov-slate: #152754;
        --gov-cream: #f3efe4;
        --gov-gold: #c79c42;
        --gov-emerald: #1d6b59;
        --gov-alert: #b02a37;
        --gov-border: rgba(255, 255, 255, 0.08);
        --gov-surface: rgba(15, 31, 63, 0.92);
    }

    .eoc-shell {
        min-height: 100vh;
        background-color: var(--gov-navy);
        color: #f5f4ee;
        overflow-x: hidden;
    }

    .eoc-card {
        background-color: rgba(13, 29, 57, 0.84);
        border: 1px solid rgba(59, 105, 255, 0.18);
        border-radius: 1.5rem;
        box-shadow: 0 24px 48px rgba(0, 0, 0, 0.22);
        backdrop-filter: blur(18px);
    }

    .eoc-header-panel {
        background: linear-gradient(135deg, rgba(12, 33, 70, 0.96), rgba(16, 44, 87, 0.88));
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.02);
    }

    .eoc-seal {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.16);
        background-color: rgba(255, 255, 255, 0.05);
        display: grid;
        place-items: center;
        text-align: center;
    }

    .eoc-seal .seal-abbrev {
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: 0.28em;
        color: var(--gov-cream);
    }

    .eoc-seal .seal-text {
        font-size: 0.72rem;
        letter-spacing: 0.14em;
        color: rgba(255, 255, 255, 0.72);
        margin-top: 0.3rem;
    }

    .eoc-title {
        font-size: clamp(2rem, 2.6vw, 3rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        margin-bottom: 0.4rem;
        color: #ffffff;
    }

    .eoc-subtitle,
    .eoc-meta-text {
        color: rgba(255, 255, 255, 0.72);
        line-height: 1.7;
    }

    .eoc-stamp {
        padding: 0.45rem 0.85rem;
        border-radius: 999px;
        background-color: rgba(199, 156, 66, 0.16);
        color: var(--gov-gold);
        font-size: 0.82rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        font-weight: 700;
    }

    .panel-surface {
        background: linear-gradient(180deg, rgba(13, 29, 57, 0.92), rgba(11, 24, 53, 0.76));
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(12px);
    }

    .eoc-status-bar {
        border-radius: 1rem;
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 1rem 1.2rem;
    }

    .eoc-kpi-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: rgba(59, 105, 255, 0.12);
        color: #ffffff;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
    }

    .eoc-kpi-card {
        border-radius: 1rem;
        background-color: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.08);
        min-height: 130px;
        transition: transform 0.18s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .eoc-kpi-card:hover {
        transform: translateY(-2px);
    }

    .eoc-kpi-title {
        font-size: 0.76rem;
        color: rgba(255, 255, 255, 0.74);
        text-transform: uppercase;
        letter-spacing: 0.12em;
        margin-bottom: 0.65rem;
    }

    .eoc-kpi-value {
        font-size: 2.1rem;
        font-weight: 800;
        color: #ffffff;
    }

    .eoc-kpi-note {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.62);
    }

    .eoc-panel-title {
        font-size: 1rem;
        font-weight: 700;
        color: #ffffff;
        margin-bottom: 0.4rem;
    }

    .eoc-panel-subtitle {
        color: rgba(255, 255, 255, 0.66);
        font-size: 0.92rem;
    }

    .eoc-table th,
    .eoc-table td {
        border-color: rgba(255, 255, 255, 0.08);
        color: rgba(255, 255, 255, 0.86);
        padding: 0.95rem 0.8rem;
    }

    .eoc-table thead th {
        font-size: 0.82rem;
        color: rgba(255, 255, 255, 0.7);
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        position: sticky;
        top: 0;
        background: rgba(13, 33, 67, 0.95);
        z-index: 1;
    }

    .eoc-table tbody tr:nth-child(odd) {
        background-color: rgba(255, 255, 255, 0.02);
    }

    .eoc-table tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    .severity-pill {
        padding: 0.35rem 0.75rem;
        font-size: 0.75rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        border-radius: 999px;
        font-weight: 700;
    }

    .severity-fire {
        background-color: rgba(176, 42, 55, 0.18);
        color: #f8d7da;
    }

    .severity-medical {
        background-color: rgba(13, 110, 253, 0.16);
        color: #cfe2ff;
    }

    .severity-rescue {
        background-color: rgba(25, 135, 84, 0.16);
        color: #d1e7dd;
    }

    .severity-crime {
        background-color: rgba(255, 193, 7, 0.18);
        color: #fff8d1;
    }

    .severity-other {
        background-color: rgba(255, 255, 255, 0.08);
        color: #ececec;
    }

    .badge-status {
        border-radius: 999px;
        padding: 0.45rem 0.85rem;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.14);
    }

    .bg-gradient {
        background-image: linear-gradient(90deg, rgba(199, 156, 66, 1), rgba(255, 235, 179, 1));
    }

    .badge-live {
        background-color: var(--gov-alert);
        color: #ffffff;
    }

    .badge-ready {
        background-color: var(--gov-emerald);
        color: #ffffff;
    }

    .badge-watch {
        background-color: #1a3c7e;
        color: #ffffff;
    }

    .badge-gold {
        background-color: var(--gov-gold);
        color: var(--gov-ink);
    }

    .live-feed-item {
        background: linear-gradient(180deg, rgba(16, 36, 72, 0.9), rgba(10, 23, 45, 0.95));
        border: 1px solid rgba(59, 105, 255, 0.16);
        border-radius: 1rem;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    }

    .live-feed-title {
        color: #ffffff;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    .live-feed-text {
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.95rem;
        line-height: 1.6;
    }

    .eoc-map-container {
        min-height: 340px;
        border-radius: 1rem;
        background-color: rgba(255, 255, 255, 0.03);
        overflow: hidden;
    }

    .chart-card {
        min-height: 360px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(16px);
    }

    .chart-card canvas {
        width: 100% !important;
        height: 280px !important;
        display: block;
    }

    .no-data-placeholder {
        min-height: 160px;
        display: grid;
        place-items: center;
        color: rgba(255, 255, 255, 0.58);
        background-color: rgba(255, 255, 255, 0.02);
        border-radius: 1rem;
    }

    .text-gold {
        color: var(--gov-gold) !important;
    }

    @media (max-width: 991px) {
        .eoc-header-panel .row>div {
            align-items: flex-start;
        }
    }

    @media (max-width: 767px) {
        .eoc-header-panel {
            padding: 1.25rem;
        }

        .eoc-status-bar,
        .eoc-kpi-card,
        .chart-card {
            min-height: auto;
        }

        .eoc-title {
            font-size: 1.7rem;
        }

        .eoc-map-container {
            min-height: 320px;
        }

        .chart-card canvas {
            height: 240px !important;
        }
    }
</style>

@php
$incidentSeverity = collect($incidentList ?? collect())->groupBy('incident_type');
$severityCounts = [
'Fire' => $incidentSeverity->get('Fire')?->count() ?? 0,
'Medical' => $incidentSeverity->get('Medical')?->count() ?? 0,
'Rescue' => $incidentSeverity->get('Rescue')?->count() ?? 0,
'Crime' => $incidentSeverity->get('Crime')?->count() ?? 0,
'Other' => collect($incidentSeverity)->reject(fn($group, $type) => in_array($type, ['Fire', 'Medical', 'Rescue', 'Crime']))->sum(fn($group) => $group->count()),
];
$activeAmbulances = max((optional($ambulanceList)->count() ?? 0) - ($availableVehicles ?? 0) - ($maintenanceVehicles ?? 0), 0);
$incidentCount = optional($incidentList)->count() ?? 0;
$operations = $recentActivities ?? collect();
@endphp

<div class="container-fluid eoc-shell py-4">
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card eoc-card eoc-header-panel p-4 h-100">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <div class="eoc-seal">
                            <div>
                                <div class="seal-abbrev">LGU</div>
                                <div class="seal-text">Seal</div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="text-uppercase text-white-50 mb-2">Emergency Operations Center</div>
                        <h1 class="eoc-title">Municipal Emergency Operations Center</h1>
                        <p class="eoc-subtitle mb-0">Command-level situational awareness for incident response, fleet readiness, and municipal emergency coordination.</p>
                    </div>
                    <div class="col-auto text-end">
                        <div class="badge-status badge-live mb-2">Operational</div>
                        <div class="text-white-50 small">{{ now()->format('F j, Y') }}</div>
                        <div class="fs-4 fw-bold mt-1">{{ now()->format('H:i:s') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card eoc-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="eoc-kpi-icon text-info"><i class="bi bi-person-badge-fill fs-4"></i></div>
                        <div>
                            <h5 class="eoc-panel-title">Duty Officer</h5>
                            <p class="eoc-panel-subtitle mb-0">{{ auth()->user()->name ?? 'Admin User' }}</p>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge-status badge-ready">Ready</span>
                        <div class="eoc-meta-text mt-2">Municipal Emergency Management Office</div>
                    </div>
                </div>
                <div class="eoc-status-bar mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-white-50">Command Status</span>
                        <span class="fw-semibold">Normal</span>
                    </div>
                    <div class="progress" style="height: 0.6rem;">
                        <div class="progress-bar bg-gold" role="progressbar" style="width: 78%;" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="small text-white-50">Operations Summary</div>
                <div class="row text-center mt-3">
                    <div class="col-6 mb-3">
                        <div class="fs-5 fw-semibold text-white">{{ $incidentCount }}</div>
                        <div class="text-white-50">Incidents</div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="fs-5 fw-semibold text-white">{{ $availableDrivers ?? 0 }}</div>
                        <div class="text-white-50">Drivers Online</div>
                    </div>
                </div>
                <div class="border-top border-white-10 pt-3 mt-3">
                    <div class="eoc-panel-title">Priority Advisories</div>
                    <p class="eoc-panel-subtitle mb-0">Always monitor live alerts and mobilize units immediately.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card eoc-card p-4 eoc-status-bar">
                <div class="row g-3 row-cols-1 row-cols-md-6">
                    <div class="col">
                        <div class="eoc-kpi-card p-3 border-start border-4 border-danger h-100">
                            <div class="d-flex align-items-start justify-content-between mb-3 gap-3">
                                <div>
                                    <div class="eoc-kpi-title">Active Incidents</div>
                                    <div class="eoc-kpi-value" data-counter="activeIncidents">{{ $activeIncidents ?? 0 }}</div>
                                </div>
                                <div class="eoc-kpi-icon text-danger"><i class="bi bi-exclamation-triangle-fill fs-5"></i></div>
                            </div>
                            <div class="eoc-kpi-note">Incident response pending</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="eoc-kpi-card p-3 border-start border-4 border-primary h-100">
                            <div class="d-flex align-items-start justify-content-between mb-3 gap-3">
                                <div>
                                    <div class="eoc-kpi-title">Active Dispatches</div>
                                    <div class="eoc-kpi-value" data-counter="activeDispatches">{{ $activeDispatches ?? 0 }}</div>
                                </div>
                                <div class="eoc-kpi-icon text-primary"><i class="bi bi-broadcast-pin fs-5"></i></div>
                            </div>
                            <div class="eoc-kpi-note">Units currently assigned</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="eoc-kpi-card p-3 border-start border-4 border-info h-100">
                            <div class="d-flex align-items-start justify-content-between mb-3 gap-3">
                                <div>
                                    <div class="eoc-kpi-title">Available Ambulances</div>
                                    <div class="eoc-kpi-value" data-counter="availableVehicles">{{ $availableVehicles ?? 0 }}</div>
                                </div>
                                <div class="eoc-kpi-icon text-info"><i class="bi bi-truck fs-5"></i></div>
                            </div>
                            <div class="eoc-kpi-note">Ready for emergency deployment</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="eoc-kpi-card p-3 border-start border-4 border-success h-100">
                            <div class="d-flex align-items-start justify-content-between mb-3 gap-3">
                                <div>
                                    <div class="eoc-kpi-title">Fleet Readiness</div>
                                    <div class="eoc-kpi-value">{{ $activeAmbulances }}</div>
                                </div>
                                <div class="eoc-kpi-icon text-success"><i class="bi bi-speedometer2 fs-5"></i></div>
                            </div>
                            <div class="eoc-kpi-note">Units currently active</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="eoc-kpi-card p-3 border-start border-4 border-warning h-100">
                            <div class="d-flex align-items-start justify-content-between mb-3 gap-3">
                                <div>
                                    <div class="eoc-kpi-title">Average Response</div>
                                    <div class="eoc-kpi-value">{{ $responseTime ?? 0 }}m</div>
                                </div>
                                <div class="eoc-kpi-icon text-warning"><i class="bi bi-clock fs-5"></i></div>
                            </div>
                            <div class="eoc-kpi-note">Minutes to arrival</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="eoc-kpi-card p-3 border-start border-4 border-secondary h-100">
                            <div class="d-flex align-items-start justify-content-between mb-3 gap-3">
                                <div>
                                    <div class="eoc-kpi-title">Open Notifications</div>
                                    <div class="eoc-kpi-value">{{ $unreadNotifications ?? 0 }}</div>
                                </div>
                                <div class="eoc-kpi-icon text-secondary"><i class="bi bi-bell fs-5"></i></div>
                            </div>
                            <div class="eoc-kpi-note">Unread command alerts</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card eoc-card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="p-4 panel-surface d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="eoc-panel-title">Live Operations Map</div>
                        <p class="eoc-panel-subtitle mb-0">Real-time tracking of ambulances and active incidents across the municipality.</p>
                    </div>
                    <span class="badge-status badge-watch">Auto-refresh 15s</span>
                </div>
                <div class="px-4 pb-3">
                    <div class="d-flex flex-wrap gap-2 small text-white-50">
                        <span class="badge bg-success-subtle text-success">● Available</span>
                        <span class="badge bg-warning-subtle text-warning">● En Route</span>
                        <span class="badge bg-danger-subtle text-danger">● Emergency</span>
                    </div>
                </div>
                <div id="liveCommandMap" class="eoc-map-container"></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card eoc-card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="p-4 panel-surface">
                    <div class="eoc-panel-title">Fleet Readiness</div>
                    <p class="eoc-panel-subtitle mb-0">Vehicle status summary for current operations.</p>
                </div>
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-white-50">Available</span>
                                <span class="fw-semibold text-white">{{ $availableVehicles ?? 0 }}</span>
                            </div>
                            <div class="progress" style="height: 0.5rem;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ min(($availableVehicles ?? 0) * 10, 100) }}%" aria-valuenow="{{ $availableVehicles ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-white-50">In active response</span>
                                <span class="fw-semibold text-white">{{ $activeAmbulances }}</span>
                            </div>
                            <div class="progress" style="height: 0.5rem;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ min($activeAmbulances * 10, 100) }}%" aria-valuenow="{{ $activeAmbulances }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-white-50">Under maintenance</span>
                                <span class="fw-semibold text-white">{{ $maintenanceVehicles ?? 0 }}</span>
                            </div>
                            <div class="progress" style="height: 0.5rem;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ min(($maintenanceVehicles ?? 0) * 10, 100) }}%" aria-valuenow="{{ $maintenanceVehicles ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-white-50">Drivers on duty</span>
                                <span class="fw-semibold text-white">{{ $availableDrivers ?? 0 }}</span>
                            </div>
                            <div class="progress" style="height: 0.5rem;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(($availableDrivers ?? 0) * 10, 100) }}%" aria-valuenow="{{ $availableDrivers ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card eoc-card border-0 shadow-sm rounded-4 overflow-hidden mt-4">
                <div class="p-4 panel-surface">
                    <div class="eoc-panel-title">Incident Severity</div>
                    <p class="eoc-panel-subtitle mb-0">Category distribution for active and recent incidents.</p>
                </div>
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="severity-pill severity-fire">Fire</div>
                        <div class="fw-semibold">{{ $severityCounts['Fire'] }}</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="severity-pill severity-medical">Medical</div>
                        <div class="fw-semibold">{{ $severityCounts['Medical'] }}</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="severity-pill severity-rescue">Rescue</div>
                        <div class="fw-semibold">{{ $severityCounts['Rescue'] }}</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="severity-pill severity-crime">Crime</div>
                        <div class="fw-semibold">{{ $severityCounts['Crime'] }}</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="severity-pill severity-other">Other</div>
                        <div class="fw-semibold">{{ $severityCounts['Other'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card eoc-card border-0 shadow-sm rounded-4 chart-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="eoc-panel-title">Response Time Analytics</div>
                        <p class="eoc-panel-subtitle mb-0">Average response and operational efficiency over the last 30 days.</p>
                    </div>
                    <span class="badge-status badge-gold">Analytics</span>
                </div>
                <div class="mb-4">
                    <div class="d-flex justify-content-between text-white-75 mb-2">
                        <span>Average arrival</span>
                        <span class="fw-semibold">{{ $responseTime ?? 0 }} minutes</span>
                    </div>
                    <div class="progress" style="height: 0.65rem;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ min(($responseTime ?? 0) * 2, 100) }}%" aria-valuenow="{{ $responseTime ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="mb-2"><canvas id="responseLoadChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card eoc-card border-0 shadow-sm rounded-4 chart-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="eoc-panel-title">Dispatch Status</div>
                        <p class="eoc-panel-subtitle mb-0">Current dispatch progress by incident state.</p>
                    </div>
                    <span class="badge-status badge-watch">Live</span>
                </div>
                <div class="mb-3"><canvas id="dispatchChart"></canvas></div>
                <div class="row text-center g-2 mt-3">
                    <div class="col-6">
                        <div class="text-white-50">Completed</div>
                        <div class="fw-semibold text-success">{{ $completedDispatches ?? 0 }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-white-50">In progress</div>
                        <div class="fw-semibold text-primary">{{ $activeDispatches ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card eoc-card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="eoc-panel-title">Live Operations Feed</div>
                        <p class="eoc-panel-subtitle mb-0">Latest mission updates, alerts and command actions.</p>
                    </div>
                    <span class="badge-status badge-live">Alerts</span>
                </div>
                @if(($activePanicAlerts ?? collect())->isNotEmpty())
                @foreach(($activePanicAlerts ?? collect())->take(5) as $alert)
                <div class="live-feed-item">
                    <div class="live-feed-title">Panic alert from {{ optional(optional($alert->driver)->user)->name ?? 'Unknown' }}</div>
                    <div class="live-feed-text">Coordinates: {{ $alert->latitude ?? 'N/A' }}, {{ $alert->longitude ?? 'N/A' }} · {{ $alert->triggered_at?->diffForHumans() }}</div>
                </div>
                @endforeach
                @else
                <div class="no-data-placeholder">No active panic alerts at this time.</div>
                @endif
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card eoc-card border-0 shadow-sm rounded-4 p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="eoc-panel-title">Operations Log</div>
                        <p class="eoc-panel-subtitle mb-0">Recent command center activities and audit entries.</p>
                    </div>
                    <span class="badge-status badge-ready">Latest</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0 eoc-table">
                        <thead>
                            <tr>
                                <th class="ps-0">Date</th>
                                <th>User</th>
                                <th class="text-end pe-0">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($operations ?? collect())->take(5) as $activity)
                            <tr>
                                <td class="ps-0 small text-white-50">{{ $activity->created_at?->format('M d, H:i') }}</td>
                                <td>{{ optional($activity->user)->name ?? 'System' }}</td>
                                <td class="text-end pe-0"><span class="badge bg-white text-dark">{{ $activity->action ?? 'No action' }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-white-50 py-4">No recent activity recorded.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<script>
    let liveMapInstance = null;
    let responseLoadChart = null;
    let dispatchChartInstance = null;

    document.addEventListener('DOMContentLoaded', function() {
        initializeLiveCommandMap();
        initializeResponseLoadChart();
        initializeDispatchChart();
    });

    function initializeLiveCommandMap() {
        const mapContainer = document.getElementById('liveCommandMap');
        if (!mapContainer) {
            return;
        }

        liveMapInstance = L.map('liveCommandMap', {
            scrollWheelZoom: false,
            zoomControl: true,
        }).setView([15.4866, 120.9675], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(liveMapInstance);

        loadMapData();
        setInterval(loadMapData, 15000);
    }

    function loadMapData() {
        if (!liveMapInstance) {
            return;
        }

        fetch("{{ route('admin.dashboard.live-command-map') }}")
            .then(response => response.json())
            .then(data => {
                liveMapInstance.eachLayer(layer => {
                    if (layer instanceof L.Marker || layer instanceof L.CircleMarker) {
                        liveMapInstance.removeLayer(layer);
                    }
                });

                const markers = [];

                if (data.ambulances && Array.isArray(data.ambulances)) {
                    data.ambulances.forEach(vehicle => {
                        if (!vehicle.latitude || !vehicle.longitude) {
                            return;
                        }

                        const marker = L.circleMarker([vehicle.latitude, vehicle.longitude], {
                            radius: 8,
                            fillColor: getStatusColor(vehicle.status_key || 'available'),
                            color: '#ffffff',
                            weight: 1.5,
                            fillOpacity: 0.9,
                        });

                        marker.bindPopup(`
                            <div style="font-size:0.9rem; min-width:180px;">
                                <strong>${vehicle.name}</strong><br>
                                Ambulance Unit: ${vehicle.plate_number || 'N/A'}<br>
                                Driver: ${vehicle.driver_name || 'Unassigned'}<br>
                                Status: ${vehicle.status || 'Unknown'}<br>
                                Last Update: ${vehicle.last_updated || 'Unknown'}
                            </div>`);

                        marker.addTo(liveMapInstance);
                        markers.push(marker.getLatLng());
                    });
                }

                if (data.drivers && Array.isArray(data.drivers)) {
                    data.drivers.forEach(driver => {
                        if (!driver.latitude || !driver.longitude) {
                            return;
                        }

                        const driverMarker = L.circleMarker([driver.latitude, driver.longitude], {
                            radius: 6,
                            fillColor: getStatusColor(driver.status_key || 'available'),
                            color: '#ffffff',
                            weight: 1,
                            fillOpacity: 0.85,
                        });

                        driverMarker.bindPopup(`
                            <div style="font-size:0.9rem; min-width:180px;">
                                <strong>${driver.driver_name || 'Driver'}</strong><br>
                                Ambulance Unit: ${driver.ambulance_unit || 'Unassigned'}<br>
                                Current Status: ${driver.status || 'Unknown'}<br>
                                Last Update: ${driver.last_updated || 'Unknown'}
                            </div>`);

                        driverMarker.addTo(liveMapInstance);
                        markers.push(driverMarker.getLatLng());
                    });
                }

                if (data.incidents && Array.isArray(data.incidents)) {
                    data.incidents.forEach(item => {
                        if (!item.latitude || !item.longitude) {
                            return;
                        }

                        const incidentMarker = L.circleMarker([item.latitude, item.longitude], {
                            radius: 9,
                            fillColor: '#b02a37',
                            color: '#ffffff',
                            weight: 1.5,
                            fillOpacity: 0.95,
                        });

                        incidentMarker.bindPopup(`
                            <div style="font-size:0.9rem; min-width:180px;">
                                <strong>${item.incident_number || 'Incident'}</strong><br>
                                Type: ${item.type || 'N/A'}<br>
                                Status: ${item.status || 'N/A'}<br>
                                Location: ${item.location || 'Unknown'}<br>
                                Last Update: ${item.last_updated || 'Unknown'}
                            </div>`);

                        incidentMarker.addTo(liveMapInstance);
                        markers.push(incidentMarker.getLatLng());
                    });
                }

                if (markers.length) {
                    const bounds = L.latLngBounds(markers);
                    liveMapInstance.fitBounds(bounds.pad(0.15));
                }
            })
            .catch(() => {
                console.warn('Unable to refresh live map data.');
            });
    }

    function getStatusColor(statusKey) {
        switch (statusKey) {
            case 'en_route':
                return '#f4b400';
            case 'emergency':
                return '#dc3545';
            default:
                return '#198754';
        }
    }

    function initializeResponseLoadChart() {
        const ctx = document.getElementById('responseLoadChart');
        if (!ctx) {
            return;
        }

        fetch("{{ route('admin.dashboard.response-load-analytics') }}")
            .then(response => response.json())
            .then(data => {
                if (responseLoadChart) {
                    responseLoadChart.destroy();
                }

                responseLoadChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Fire', 'Medical', 'Rescue', 'Crime'],
                        datasets: [{
                            data: [data.Fire || 0, data.Medical || 0, data.Rescue || 0, data.Crime || 0],
                            backgroundColor: ['#b02a37', '#0d6efd', '#198754', '#f59f00'],
                            borderColor: '#0d214b',
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#f7f7f2'
                                }
                            }
                        }
                    }
                });
            })
            .catch(() => {
                console.warn('Unable to load response analytics.');
            });
    }

    function initializeDispatchChart() {
        const ctx = document.getElementById('dispatchChart');
        if (!ctx) {
            return;
        }

        const chartData = <?php echo json_encode($dispatchChart ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        dispatchChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(chartData),
                datasets: [{
                    data: Object.values(chartData),
                    backgroundColor: ['#0d6efd', '#198754'],
                    borderColor: '#0d214b',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#f7f7f2'
                        }
                    }
                }
            }
        });
    }
</script>
@endsection