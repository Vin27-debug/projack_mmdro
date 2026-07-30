

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">Driver Performance Analytics</h2>
            <p class="text-muted mb-0">Track response efficiency, completion rates, and incident handling by driver.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('admin.reports.driver-performance.pdf')); ?>" class="btn btn-outline-danger">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <a href="<?php echo e(route('admin.reports.driver-performance.excel')); ?>" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Completed Dispatches</div>
                    <div class="display-6 fw-bold mt-2"><?php echo e($drivers->sum('completed_dispatches')); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Average Response Time</div>
                    <div class="display-6 fw-bold mt-2"><?php echo e(number_format($drivers->avg('average_response_time'), 1)); ?> min</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Average Arrival Time</div>
                    <div class="display-6 fw-bold mt-2"><?php echo e(number_format($drivers->avg('average_arrival_time'), 1)); ?> min</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Completion Rate</div>
                    <div class="display-6 fw-bold mt-2"><?php echo e(number_format($drivers->avg('completion_rate'), 1)); ?>%</div>
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
                        <?php $__empty_1 = true; $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($driver->user?->name ?? 'Unknown Driver'); ?></td>
                            <td><?php echo e($driver->badge_id); ?></td>
                            <td><?php echo e($driver->completed_dispatches); ?></td>
                            <td><?php echo e($driver->average_response_time); ?> min</td>
                            <td><?php echo e($driver->average_arrival_time); ?> min</td>
                            <td><?php echo e($driver->completion_rate); ?>%</td>
                            <td><?php echo e($driver->incident_count); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No driver data found.</td>
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
        const ctx = document.getElementById('driverTrendChart');
        if (!ctx) return;

        const labels = <?php echo json_encode($monthlyChart['labels'] ?? [], 15, 512) ?>;
        const values = <?php echo json_encode($monthlyChart['series'] ?? [], 15, 512) ?>;

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
<?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/admin/reports/driver-performance.blade.php ENDPATH**/ ?>