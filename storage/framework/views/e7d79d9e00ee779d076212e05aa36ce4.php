

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-4">
        <div>
            <h2 class="section-heading mb-1">Emergency Reports Center</h2>
            <p class="section-excerpt mb-0">Operational metrics, response analytics, and export-ready incident intelligence.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?php echo e(route('admin.reports.center.export.pdf', $filters)); ?>" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <a href="<?php echo e(route('admin.reports.center.export.excel', $filters)); ?>" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <form method="GET" action="<?php echo e(route('admin.reports.center')); ?>" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" value="<?php echo e($filters['start_date'] ?? ''); ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" value="<?php echo e($filters['end_date'] ?? ''); ?>" class="form-control">
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
                    <div class="display-6 fw-bold mt-2"><?php echo e($summary['total_incidents']); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="small text-uppercase fw-semibold">Completed</div>
                    <div class="display-6 fw-bold mt-2"><?php echo e($summary['completed_incidents']); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="small text-uppercase fw-semibold">Pending</div>
                    <div class="display-6 fw-bold mt-2"><?php echo e($summary['pending_incidents']); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="small text-uppercase fw-semibold">Active</div>
                    <div class="display-6 fw-bold mt-2"><?php echo e($summary['active_incidents']); ?></div>
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
                    <canvas id="incidentTrendChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Response Time Snapshot</h5>
                    <div class="mb-3">
                        <div class="text-muted small">Average</div>
                        <div class="fw-bold fs-5"><?php echo e($responseTimeMetrics['average_response_time']); ?> min</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Fastest</div>
                        <div class="fw-bold fs-5"><?php echo e($responseTimeMetrics['fastest_response']); ?> min</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Slowest</div>
                        <div class="fw-bold fs-5"><?php echo e($responseTimeMetrics['slowest_response']); ?> min</div>
                    </div>
                    <div>
                        <div class="text-muted small">Completed Responses</div>
                        <div class="fw-bold fs-5"><?php echo e($responseTimeMetrics['completed_responses']); ?></div>
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
                                <?php $__empty_1 = true; $__currentLoopData = $driverPerformance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($item->driver?->user?->name ?? 'Driver'); ?></td>
                                    <td><?php echo e($item->dispatch_count); ?></td>
                                    <td><?php echo e($item->average_response_time); ?> min</td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="text-muted">No driver data found.</td>
                                </tr>
                                <?php endif; ?>
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
                                <?php $__empty_1 = true; $__currentLoopData = $vehicleUtilization; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($item->ambulance?->vehicle_name ?? 'Vehicle'); ?></td>
                                    <td><?php echo e($item->total_dispatches); ?></td>
                                    <td><?php echo e($item->availability_rate); ?>%</td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="text-muted">No utilization data found.</td>
                                </tr>
                                <?php endif; ?>
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
                        <?php $__empty_1 = true; $__currentLoopData = $incidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($incident->incident_number); ?></td>
                            <td><?php echo e($incident->reporter_name); ?></td>
                            <td><?php echo e($incident->incident_type); ?></td>
                            <td><?php echo e($incident->location); ?></td>
                            <td><span class="badge bg-secondary"><?php echo e(ucfirst($incident->status)); ?></span></td>
                            <td><?php echo e($incident->created_at?->format('M d, Y H:i')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-muted">No incidents found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $__env->startSection('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('incidentTrendChart');
        if (!ctx) return;

        const labels = <?php echo json_encode($monthlyTrends['labels'] ?? [], 15, 512) ?>;
        const values = <?php echo json_encode($monthlyTrends['series'] ?? [], 15, 512) ?>;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
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
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/admin/reports-center.blade.php ENDPATH**/ ?>