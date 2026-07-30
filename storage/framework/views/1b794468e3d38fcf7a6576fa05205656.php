

<?php $__env->startSection('content'); ?>

<?php if(isset($activePanicAlerts) && $activePanicAlerts->count()): ?>

<div class="alert alert-danger shadow-sm mb-4">

    <h3>🚨 ACTIVE PANIC ALERTS</h3>

    <?php $__currentLoopData = $activePanicAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

    <div class="border-bottom pb-2 mb-2">

        <strong>
            <?php echo e($alert->driver->user->name ?? 'Unknown Driver'); ?>

        </strong>

        <br>

        Latitude:
        <?php echo e($alert->latitude); ?>


        <br>

        Longitude:
        <?php echo e($alert->longitude); ?>


        <br>

        Time:
        <?php echo e($alert->triggered_at); ?>


    </div>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</div>

<?php endif; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Superadmin Dashboard</h1>
        <p class="text-muted mb-0">Monitor incidents, drivers, and ambulance availability in one place.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted">Total Incidents</h5>
                <p class="display-6 fw-bold mb-0"><?php echo e($stats['total_incidents']); ?></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted">Pending Incidents</h5>
                <p class="display-6 fw-bold mb-0"><?php echo e($stats['pending_incidents']); ?></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted">Dispatched Incidents</h5>
                <p class="display-6 fw-bold mb-0"><?php echo e($stats['dispatched_incidents']); ?></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted">Completed Incidents</h5>
                <p class="display-6 fw-bold mb-0"><?php echo e($stats['completed_incidents']); ?></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted">Total Drivers</h5>
                <p class="display-6 fw-bold mb-0"><?php echo e($stats['total_drivers']); ?></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title text-muted">Available Ambulances</h5>
                <p class="display-6 fw-bold mb-0"><?php echo e($stats['available_ambulances']); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0">Recent Incidents</h2>

    <a href="#" class="btn btn-sm btn-primary">
        View All
    </a>
</div>
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Incident Number</th>
                <th>Reporter</th>
                <th>Type</th>
                <th>Location</th>
                <th>Status</th>
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