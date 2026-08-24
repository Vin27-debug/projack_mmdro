@extends('layouts.admin')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-4">
        <div>
            <h2 class="section-heading mb-1">Emergency Reports Center</h2>
            <p class="section-excerpt mb-0">Operational metrics, response analytics, and export-ready incident intelligence.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.reports.center.export.pdf', $filters) }}" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <a href="{{ route('admin.reports.center.export.excel', $filters) }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            <button type="button" onclick="window.print()" class="btn btn-outline-dark">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.reports.center') }}" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="small text-uppercase fw-semibold">Total Incidents</div>
                    <div class="display-6 fw-bold mt-2">{{ $summary['total_incidents'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="small text-uppercase fw-semibold">Completed</div>
                    <div class="display-6 fw-bold mt-2">{{ $summary['completed_incidents'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="small text-uppercase fw-semibold">Pending</div>
                    <div class="display-6 fw-bold mt-2">{{ $summary['pending_incidents'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="small text-uppercase fw-semibold">Active</div>
                    <div class="display-6 fw-bold mt-2">{{ $summary['active_incidents'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Monthly Incident Trends</h5>
                        <span class="badge bg-primary text-white">Chart.js</span>
                    </div>
                    <div class="chart-container">
                        <canvas id="incidentTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Response Time Snapshot</h5>
                    <div class="mb-3">
                        <div class="text-muted small">Average</div>
                        <div class="fw-bold fs-5">{{ $responseTimeMetrics['average_response_time'] }} min</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Fastest</div>
                        <div class="fw-bold fs-5">{{ $responseTimeMetrics['fastest_response'] }} min</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Slowest</div>
                        <div class="fw-bold fs-5">{{ $responseTimeMetrics['slowest_response'] }} min</div>
                    </div>
                    <div>
                        <div class="text-muted small">Completed Responses</div>
                        <div class="fw-bold fs-5">{{ $responseTimeMetrics['completed_responses'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Driver Performance</h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Driver</th>
                                    <th>Dispatches</th>
                                    <th>Avg. Resp.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($driverPerformance as $item)
                                <tr>
                                    <td>{{ $item->driver?->user?->name ?? 'Driver' }}</td>
                                    <td>{{ $item->dispatch_count }}</td>
                                    <td>{{ $item->average_response_time }} min</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-muted">No driver data found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Vehicle Utilization</h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>Dispatches</th>
                                    <th>Availability</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehicleUtilization as $item)
                                <tr>
                                    <td>{{ $item->ambulance?->vehicle_name ?? 'Vehicle' }}</td>
                                    <td>{{ $item->total_dispatches }}</td>
                                    <td>{{ $item->availability_rate }}%</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-muted">No utilization data found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Incident Summary</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Incident #</th>
                            <th>Reporter</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidents as $incident)
                        <tr>
                            <td>{{ $incident->incident_number }}</td>
                            <td>{{ $incident->reporter_name }}</td>
                            <td>{{ $incident->incident_type }}</td>
                            <td>{{ $incident->location }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($incident->status) }}</span></td>
                            <td>{{ $incident->created_at?->format('M d, Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-muted">No incidents found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .chart-container {
        position: relative;
        width: 100%;
        height: 300px;
    }

    #incidentTrendChart {
        width: 100% !important;
        height: 100% !important;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('incidentTrendChart');

        if (!ctx) {
            return;
        }

        const labels = @json($monthlyTrends['labels'] ?? []);
        const values = @json($monthlyTrends['series'] ?? []);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Incidents',
                    data: values,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.18)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>

@endsection