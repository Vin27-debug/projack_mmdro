

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <div>
        <div class="small text-uppercase text-white-50 mb-2">Driver Management</div>
        <h1 class="page-title">Approved Drivers</h1>
        <p class="page-subtitle mb-0">All drivers currently approved for dispatch and monitoring.</p>
    </div>
    <a href="<?php echo e(route('superadmin.dashboard')); ?>" class="btn btn-outline-light page-back-button">
        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
    </a>
</div>

<div class="card admin-card border-0 shadow-sm p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Badge</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>License</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>#<?php echo e($driver->id); ?></td>
                    <td><?php echo e($driver->badge_id); ?></td>
                    <td><?php echo e($driver->user->name); ?></td>
                    <td><?php echo e($driver->user->email); ?></td>
                    <td><?php echo e($driver->license_number); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No approved drivers found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/superadmin/drivers/index.blade.php ENDPATH**/ ?>