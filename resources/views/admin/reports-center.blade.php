@extends('layouts.admin')

@section('content')
<style>
    .reports-page {
        max-width: 1600px;
        margin: 0 auto;
    }

    .reports-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.14);
    }

    .reports-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #fff;
    }

    .reports-lead {
        color: rgba(255, 255, 255, 0.68);
        font-size: 1rem;
    }

    .reports-surface {
        background: #0b2043;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 4px;
    }

    .reports-filter {
        padding: 1.25rem;
    }

    .reports-filter .form-label {
        color: #fff;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .reports-filter .form-control,
    .reports-filter .form-select {
        min-height: 44px;
        font-size: 0.95rem;
    }

    .reports-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .reports-actions .btn {
        min-height: 44px;
        font-weight: 600;
    }

    .report-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.75rem;
        background: #061633;
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 4px;
    }

    .report-tab {
        min-height: 44px;
        padding: 0.55rem 1rem;
        border: 1px solid rgba(255, 255, 255, 0.24);
        border-radius: 3px;
        background: transparent;
        color: #fff;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .report-tab:hover,
    .report-tab:focus-visible {
        background: rgba(1, 76, 253, 0.16);
        color: #fff;
    }

    .report-tab.active {
        background: #014cfd;
        border-color: #014cfd;
        color: #fff;
    }

    .report-section {
        display: none;
    }

    .report-section.active {
        display: block;
    }

    .report-section-title {
        color: #fff;
        font-size: 1.35rem;
        font-weight: 600;
    }

    .report-section-help {
        color: rgba(255, 255, 255, 0.68);
        font-size: 0.95rem;
    }

    .report-stat {
        min-height: 108px;
        padding: 1rem;
        border-left: 3px solid #014cfd;
    }

    .report-stat.success {
        border-left-color: #2aa876;
    }

    .report-stat.warning {
        border-left-color: #f0ad00;
    }

    .report-stat.danger {
        border-left-color: #dc3545;
    }

    .report-stat-label {
        color: rgba(255, 255, 255, 0.68);
        font-size: 0.88rem;
    }

    .report-stat-value {
        margin-top: 0.35rem;
        color: #fff;
        font-size: 1.8rem;
        font-weight: 700;
    }

    .report-table {
        margin-bottom: 0;
        font-size: 0.92rem;
    }

    .report-table th {
        color: #fff !important;
        font-size: 0.85rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .report-table td {
        color: rgba(255, 255, 255, 0.86) !important;
        vertical-align: middle;
    }

    .report-table .empty-row {
        padding: 1.5rem;
        color: rgba(255, 255, 255, 0.68) !important;
        text-align: center;
    }

    .status-label {
        display: inline-block;
        padding: 0.25rem 0.45rem;
        border: 1px solid rgba(255, 255, 255, 0.24);
        border-radius: 3px;
        font-size: 0.8rem;
    }

    .report-chart {
        height: 260px;
    }

    .report-note {
        color: rgba(255, 255, 255, 0.68);
        font-size: 0.9rem;
    }

    @media (max-width: 767px) {
        .reports-title {
            font-size: 1.45rem;
        }

        .report-tab {
            flex: 1 1 calc(50% - 0.5rem);
        }

        .reports-actions .btn {
            flex: 1 1 100%;
        }
    }
</style>

@php
$fleetVehicles = collect($vehicleUtilization ?? []);
$fleetTotal = $fleetVehicles->count();
$fleetAvailable = $fleetVehicles->where('ambulance.status', 'available')->count();
$fleetAssigned = $fleetVehicles->where('ambulance.status', 'on_duty')->count();
$fleetMaintenance = $fleetVehicles->where('ambulance.status', 'maintenance')->count();
$activeTab = request('section', 'overview');
$validTabs = ['overview', 'response-time', 'incidents', 'fleet'];
$activeTab = in_array($activeTab, $validTabs, true) ? $activeTab : 'overview';
@endphp

<div class="reports-page">
    <header class="reports-header pb-3 mb-4">
        <h1 class="reports-title mb-1">Reports Center</h1>
        <p class="reports-lead mb-0">View incident, response-time, and fleet information in one place.</p>
    </header>

    <form method="GET" action="{{ route('admin.reports.center') }}" class="reports-surface reports-filter mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-sm-6 col-lg-2">
                <label for="start_date" class="form-label">Date From</label>
                <input id="start_date" type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="form-control">
            </div>
            <div class="col-sm-6 col-lg-2">
                <label for="end_date" class="form-label">Date To</label>
                <input id="end_date" type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="form-control">
            </div>
            <div class="col-sm-6 col-lg-2">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach(\App\Models\Incident::VALID_STATUSES as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '' )===$status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-lg-3">
                <label for="incident_type" class="form-label">Incident Type</label>
                <select id="incident_type" name="incident_type" class="form-select">
                    <option value="">All incident types</option>
                    @foreach(\App\Models\Incident::INCIDENT_TYPES as $type)
                    <option value="{{ $type }}" @selected(($filters['incident_type'] ?? '' )===$type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <div class="reports-actions">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('admin.reports.center') }}" class="btn btn-outline-light">Clear Filters</a>
                </div>
            </div>
        </div>
    </form>

    <nav class="report-tabs mb-4" aria-label="Report sections">
        <button type="button" class="report-tab {{ $activeTab === 'overview' ? 'active' : '' }}" data-report-tab="overview">View Overview</button>
        <button type="button" class="report-tab {{ $activeTab === 'response-time' ? 'active' : '' }}" data-report-tab="response-time">View Response Time</button>
        <button type="button" class="report-tab {{ $activeTab === 'incidents' ? 'active' : '' }}" data-report-tab="incidents">View Incidents</button>
        <button type="button" class="report-tab {{ $activeTab === 'fleet' ? 'active' : '' }}" data-report-tab="fleet">View Fleet</button>
    </nav>

    <section class="report-section {{ $activeTab === 'overview' ? 'active' : '' }}" data-report-section="overview" aria-labelledby="overview-title">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
            <div>
                <h2 id="overview-title" class="report-section-title mb-1">Overview</h2>
                <p class="report-section-help mb-0">A quick summary of the current report filters.</p>
            </div>
            <div class="reports-actions"><a href="{{ route('admin.reports.center.export.pdf', $filters) }}" class="btn btn-outline-light">Export PDF</a><a href="{{ route('admin.reports.center.export.excel', $filters) }}" class="btn btn-outline-light">Export Excel</a></div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="reports-surface report-stat">
                    <div class="report-stat-label">Total incidents</div>
                    <div class="report-stat-value">{{ $summary['total_incidents'] ?? 0 }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="reports-surface report-stat danger">
                    <div class="report-stat-label">Open incidents</div>
                    <div class="report-stat-value">{{ $summary['active_incidents'] ?? 0 }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="reports-surface report-stat success">
                    <div class="report-stat-label">Completed incidents</div>
                    <div class="report-stat-value">{{ $summary['completed_incidents'] ?? 0 }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="reports-surface report-stat warning">
                    <div class="report-stat-label">Available vehicles</div>
                    <div class="report-stat-value">{{ $fleetAvailable }}</div>
                </div>
            </div>
        </div>
        <div class="reports-surface p-3">
            <h3 class="h5 text-white mb-3">Incident summary</h3>
            @include('admin.reports-center-incidents-table', ['incidents' => $incidents, 'compact' => true])
        </div>
    </section>

    <section class="report-section {{ $activeTab === 'response-time' ? 'active' : '' }}" data-report-section="response-time" aria-labelledby="response-title">
        <div class="mb-3">
            <h2 id="response-title" class="report-section-title mb-1">Response Time</h2>
            <p class="report-section-help mb-0">Time recorded between receiving a call, responding, and arriving at the scene.</p>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="reports-surface report-stat warning">
                    <div class="report-stat-label">Average response time</div>
                    <div class="report-stat-value">{{ $responseTimeMetrics['average_response_time'] ?? 0 }} min</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="reports-surface report-stat success">
                    <div class="report-stat-label">Fastest response</div>
                    <div class="report-stat-value">{{ $responseTimeMetrics['fastest_response'] ?? 0 }} min</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="reports-surface report-stat danger">
                    <div class="report-stat-label">Slowest response</div>
                    <div class="report-stat-value">{{ $responseTimeMetrics['slowest_response'] ?? 0 }} min</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="reports-surface report-stat">
                    <div class="report-stat-label">Completed responses</div>
                    <div class="report-stat-value">{{ $responseTimeMetrics['completed_responses'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-xl-5">
                <div class="reports-surface p-3 h-100">
                    <h3 class="h5 text-white mb-3">Monthly incident volume</h3>
                    <div class="report-chart"><canvas id="incidentTrendChart"></canvas></div>
                </div>
            </div>
            <div class="col-xl-7">
                <div class="reports-surface p-3">
                    <h3 class="h5 text-white mb-3">Response details</h3>
                    <div class="table-responsive">
                        <table class="table report-table align-middle">
                            <thead>
                                <tr>
                                    <th>Incident</th>
                                    <th>Type</th>
                                    <th>Call received</th>
                                    <th>At scene</th>
                                    <th>Call to scene</th>
                                    <th>Response to scene</th>
                                </tr>
                            </thead>
                            <tbody>@forelse($responseTimeMetrics['dispatches'] ?? [] as $dispatch) @php($incident = $dispatch->incident) <tr>
                                    <td>{{ $incident?->incident_number ?? 'N/A' }}</td>
                                    <td>{{ $incident?->incident_type ?? 'N/A' }}</td>
                                    <td>{{ $incident?->call_received_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                                    <td>{{ $incident?->at_scene_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
                                    <td>{{ $incident && $incident->call_received_at && $incident->at_scene_at ? $incident->call_received_at->diffInMinutes($incident->at_scene_at) . ' min' : 'N/A' }}</td>
                                    <td>{{ $incident && $incident->response_at && $incident->at_scene_at ? $incident->response_at->diffInMinutes($incident->at_scene_at) . ' min' : 'N/A' }}</td>
                                </tr> @empty<tr>
                                    <td colspan="6" class="empty-row">No response-time records found.</td>
                                </tr>@endforelse</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="report-section {{ $activeTab === 'incidents' ? 'active' : '' }}" data-report-section="incidents" aria-labelledby="incidents-title">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
            <div>
                <h2 id="incidents-title" class="report-section-title mb-1">Incident Reports</h2>
                <p class="report-section-help mb-0">Review incident type, priority, status, location, and date.</p>
            </div>
            <div class="reports-actions"><a href="{{ route('admin.reports.center.export.pdf', $filters) }}" class="btn btn-outline-light">Export PDF</a><a href="{{ route('admin.reports.center.export.excel', $filters) }}" class="btn btn-outline-light">Export Excel</a></div>
        </div>
        <div class="reports-surface p-3">@include('admin.reports-center-incidents-table', ['incidents' => $incidents, 'compact' => false])</div>
    </section>

    <section class="report-section {{ $activeTab === 'fleet' ? 'active' : '' }}" data-report-section="fleet" aria-labelledby="fleet-title">
        <div class="mb-3">
            <h2 id="fleet-title" class="report-section-title mb-1">Fleet Reports</h2>
            <p class="report-section-help mb-0">Current vehicle availability and utilization.</p>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="reports-surface report-stat">
                    <div class="report-stat-label">Total vehicles</div>
                    <div class="report-stat-value">{{ $fleetTotal }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="reports-surface report-stat success">
                    <div class="report-stat-label">Available vehicles</div>
                    <div class="report-stat-value">{{ $fleetAvailable }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="reports-surface report-stat">
                    <div class="report-stat-label">Assigned vehicles</div>
                    <div class="report-stat-value">{{ $fleetAssigned }}</div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="reports-surface report-stat warning">
                    <div class="report-stat-label">Maintenance vehicles</div>
                    <div class="report-stat-value">{{ $fleetMaintenance }}</div>
                </div>
            </div>
        </div>
        <div class="reports-surface p-3">
            <div class="table-responsive">
                <table class="table report-table align-middle">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Dispatches</th>
                            <th>Availability</th>
                        </tr>
                    </thead>
                    <tbody>@forelse($fleetVehicles as $item) @php($vehicle = $item->ambulance) <tr>
                            <td>{{ $vehicle?->vehicle_name ?? 'Vehicle' }}
                                <div class="report-note">{{ $vehicle?->plate_number ?? 'No plate number' }}</div>
                            </td>
                            <td>{{ ucwords(str_replace('_', ' ', $vehicle?->vehicle_type ?? 'Vehicle')) }}</td>
                            <td><span class="status-label">{{ ucfirst(str_replace('_', ' ', $vehicle?->status ?? 'Unknown')) }}</span></td>
                            <td>{{ $item->total_dispatches }}</td>
                            <td>{{ $item->availability_rate }}%</td>
                        </tr>@empty<tr>
                            <td colspan="5" class="empty-row">No vehicle records found.</td>
                        </tr>@endforelse</tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('[data-report-tab]');
        const sections = document.querySelectorAll('[data-report-section]');
        const showSection = (name) => {
            tabs.forEach(tab => {
                const selected = tab.dataset.reportTab === name;
                tab.classList.toggle('active', selected);
                tab.setAttribute('aria-pressed', selected ? 'true' : 'false');
            });
            sections.forEach(section => section.classList.toggle('active', section.dataset.reportSection === name));
        };
        tabs.forEach(tab => tab.addEventListener('click', () => showSection(tab.dataset.reportTab)));

        const chartCanvas = document.getElementById('incidentTrendChart');
        if (chartCanvas && typeof Chart !== 'undefined') {
            new Chart(chartCanvas, {
                type: 'line',
                data: {
                    labels: @json($monthlyTrends['labels'] ?? []),
                    datasets: [{
                        label: 'Incidents',
                        data: @json($monthlyTrends['series'] ?? []),
                        borderColor: '#014cfd',
                        backgroundColor: 'rgba(1, 76, 253, 0.14)',
                        fill: true,
                        tension: 0.2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#eef4ff',
                                precision: 0
                            }
                        },
                        x: {
                            ticks: {
                                color: '#eef4ff'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#eef4ff'
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection