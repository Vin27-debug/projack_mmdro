

<?php $__env->startSection('content'); ?>

<div class="container-fluid eoc-shell py-4">

    <?php if(isset($activePanicAlerts) && $activePanicAlerts->count()): ?>
    <div class="card eoc-card border-0 shadow-sm mb-4 p-4">
        <div class="d-flex flex-column flex-md-row align-items-start justify-content-between gap-3 mb-3">
            <div>
                <div class="small text-uppercase text-white-50 mb-2">Active Panic Alerts</div>
                <h2 class="eoc-title mb-1">Emergency Alert Feed</h2>
                <p class="eoc-panel-subtitle mb-0">Immediate response details for current panic triggers.</p>
            </div>
            <span class="badge-status live align-self-start">Critical</span>
        </div>

        <?php $__currentLoopData = $activePanicAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="border-bottom border-white-10 pb-3 mb-3">
            <div class="fw-semibold text-white"><?php echo e($alert->driver->user->name ?? 'Unknown Driver'); ?></div>
            <div class="small text-white-50">Latitude: <?php echo e($alert->latitude); ?></div>
            <div class="small text-white-50">Longitude: <?php echo e($alert->longitude); ?></div>
            <div class="small text-white-50">Time: <?php echo e($alert->triggered_at); ?></div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <div class="card eoc-card border-0 shadow-sm mb-4 p-4 admin-header-panel">
        <div class="row g-3 align-items-center">
            <div class="col">
                <div class="small text-uppercase text-white-50 mb-2">Super Admin Operations</div>
                <h1 class="eoc-title mb-1">Command Dashboard</h1>
                <p class="eoc-panel-subtitle mb-0">Monitor incidents, drivers, and ambulance availability in one place.</p>
            </div>
            <div class="col-auto text-end">
                <span class="badge-status ready mb-2">Operational</span>
                <div class="text-white-50 small"><?php echo e(now()->format('F j, Y')); ?></div>
                <div class="fs-4 fw-bold mt-1"><?php echo e(now()->format('H:i')); ?></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card admin-card border-0 h-100 p-4">
                <div class="eoc-panel-title">Total Incidents</div>
                <div class="display-6 fw-bold text-white"><?php echo e($stats['total_incidents']); ?></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card admin-card border-0 h-100 p-4">
                <div class="eoc-panel-title">Pending Incidents</div>
                <div class="display-6 fw-bold text-white"><?php echo e($stats['pending_incidents']); ?></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card admin-card border-0 h-100 p-4">
                <div class="eoc-panel-title">Dispatched Incidents</div>
                <div class="display-6 fw-bold text-white"><?php echo e($stats['dispatched_incidents']); ?></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card admin-card border-0 h-100 p-4">
                <div class="eoc-panel-title">Completed Incidents</div>
                <div class="display-6 fw-bold text-white"><?php echo e($stats['completed_incidents']); ?></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card admin-card border-0 h-100 p-4">
                <div class="eoc-panel-title">Total Drivers</div>
                <div class="display-6 fw-bold text-white"><?php echo e($stats['total_drivers']); ?></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card admin-card border-0 h-100 p-4">
                <div class="eoc-panel-title">Available Ambulances</div>
                <div class="display-6 fw-bold text-white"><?php echo e($stats['available_ambulances']); ?></div>
            </div>
        </div>
    </div>

    <div class="card admin-card border-0 shadow-sm rounded-4 p-4">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-3">
            <div>
                <h2 class="h5 mb-1 text-white">Recent Incidents</h2>
                <p class="eoc-panel-subtitle mb-0">Latest incident updates from the municipal command center.</p>
            </div>
            <a href="#" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-borderless align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-white-50">Incident Number</th>
                        <th class="text-white-50">Reporter</th>
                        <th class="text-white-50">Type</th>
                        <th class="text-white-50">Location</th>
                        <th class="text-white-50">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $recentIncidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($incident->incident_number); ?></td>
                        <td><?php echo e($incident->reporter_name); ?></td>
                        <td><?php echo e($incident->incident_type); ?></td>
                        <td><?php echo e($incident->location); ?></td>
                        <td><span class="badge bg-secondary text-uppercase"><?php echo e($incident->status); ?></span></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No incidents recorded yet.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/superadmin/dashboard.blade.php ENDPATH**/ ?>