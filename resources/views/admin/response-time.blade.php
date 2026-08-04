@extends('layouts.admin')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="section-heading mb-1">Response Time Analytics</h2>
            <p class="section-excerpt mb-0">Monitor operational performance using dispatch arrival timing.</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Average Response Time</div>
                    <div class="display-6 fw-bold mt-2">{{ number_format($averageResponseTime, 2) }} min</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Fastest Response</div>
                    <div class="display-6 fw-bold mt-2">{{ $fastestResponse }} min</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Slowest Response</div>
                    <div class="display-6 fw-bold mt-2">{{ $slowestResponse }} min</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Total Completed Responses</div>
                    <div class="display-6 fw-bold mt-2">{{ $completedResponses }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Response Time Trend</h5>
                        <span class="badge bg-primary text-white">Monthly average</span>
                    </div>
                    <canvas id="responseTimeChart" height="220"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Recent Completed Dispatches</h5>
                    <div class="list-group list-group-flush">
                        @forelse($dispatches->take(8) as $dispatch)
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="fw-semibold">{{ $dispatch->incident->incident_number ?? 'N/A' }}</div>
                                    <div class="small text-muted">{{ $dispatch->driver->user->name ?? 'Unassigned' }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="small fw-semibold">{{ $dispatch->assigned_at && $dispatch->arrived_at ? $dispatch->assigned_at->diffInMinutes($dispatch->arrived_at) : 0 }} min</div>
                                    <div class="small text-muted">{{ $dispatch->arrived_at?->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-muted small">No completed dispatches with response timing available.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('responseTimeChart');

        if (!ctx) {
            return;
        }

        const labels = @json($labels);
        const data = @json($series);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Average Response Time (min)',
                    data: data,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.18)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
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