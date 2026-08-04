

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="section-heading mb-1">New Maintenance Record</h2>
            <p class="section-excerpt mb-0">Log a scheduled repair or check-up for a vehicle.</p>
        </div>
        <a href="<?php echo e(route('admin.maintenance.index')); ?>" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?php echo e(route('admin.maintenance.store')); ?>">
                <?php echo csrf_field(); ?>
                <?php echo $__env->make('admin.maintenance._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/admin/maintenance/create.blade.php ENDPATH**/ ?>