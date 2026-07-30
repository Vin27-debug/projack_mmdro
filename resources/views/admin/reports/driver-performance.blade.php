@extends('layouts.admin')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">Driver Performance Analytics</h2>
            <p class="text-muted mb-0">Track response efficiency, completion rates, and incident handling by driver.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.driver-performance.pdf') }}" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <a href="{{ route('admin.reports.driver-performance.excel') }}" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Completed Dispatches</div>
                    <div class="display-6 fw-bold mt-2">{{ $drivers->sum('completed_dispatches') }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Average Response Time</div>
                    <div class="display-6 fw-bold mt-2">{{ number_format($drivers->avg('average_response_time'), 1) }} min</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Average Arrival Time</div>
                    <div class="display-6 fw-bold mt-2">{{ number_format($drivers->avg('average_arrival_time'), 1) }} min</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Completion Rate</div>
                    <div class="display-6 fw-bold mt-2">{{ number_format($drivers->avg('completion_rate'), 1) }}%</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Monthly Dispatch Trend</h5>
            <canvas id="driverTrendChart" height="220"></canvas>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Driver Leaderboard</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Driver</th>
                            <th>Badge ID</th>
                            <th>Completed Dispatches</th>
                            <th>Avg. Response Time</th>
                            <th>Avg. Arrival Time</th>
                            <th>Completion Rate</th>
                            <th>Incident Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $driver)
                        <tr>
                            <td>{{ $driver->user?->name ?? 'Unknown Driver' }}</td>
                            <td>{{ $driver->badge_id }}</td>
                            <td>{{ $driver->completed_dispatches }}</td>
                            <td>{{ $driver->average_response_time }} min</td>
                            <td>{{ $driver->average_arrival_time }} min</td>
                            <td>{{ $driver->completion_rate }}%</td>
                            <td>{{ $driver->incident_count }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No driver data found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('driverTrendChart');
        if (!ctx) return;

        const labels = @json($monthlyChart['labels'] ?? []);
        const values = @json($monthlyChart['series'] ?? []);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Completed Dispatches',
                    data: values,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.18)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
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
@endsection