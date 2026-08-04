

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="section-heading mb-1">Vehicle Maintenance</h2>
            <p class="section-excerpt mb-0">Track vehicle upkeep, maintenance history, and availability.</p>
        </div>
        <a href="<?php echo e(route('admin.maintenance.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Maintenance Record
        </a>
    </div>

    <?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="row g-4 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-4 border-primary shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="text-uppercase small text-muted">Total Vehicles</div>
                    <div class="display-6 fw-bold text-white mt-2">
                        <?php echo e($stats['total_vehicles']); ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-4 border-success shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="text-uppercase small text-muted">Active Vehicles</div>
                    <div class="display-6 fw-bold text-white mt-2">
                        <?php echo e($stats['active_vehicles']); ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-4 border-warning shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="text-uppercase small text-muted">Vehicles Under Maintenance</div>
                    <div class="display-6 fw-bold text-white mt-2">
                        <?php echo e($stats['maintenance_vehicles']); ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-start border-4 border-info shadow-sm h-100 admin-card">
                <div class="card-body">
                    <div class="text-uppercase small text-muted">Available Vehicles</div>
                    <div class="display-6 fw-bold text-white mt-2">
                        <?php echo e($stats['available_vehicles']); ?>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Vehicle</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Cost</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $maintenances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maintenance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($maintenance->ambulance?->vehicle_name ?? 'Unknown vehicle'); ?></td>
                            <td><?php echo e($maintenance->scheduled_date?->format('M d, Y')); ?></td>
                            <td><?php echo e($maintenance->maintenance_type); ?></td>
                            <td>$<?php echo e(number_format(0, 2)); ?></td>
                            <td><?php echo e($maintenance->description ?: '—'); ?></td>
                            <td>
                                <?php if($maintenance->status === 'completed'): ?>
                                <span class="badge bg-success">Completed</span>
                                <?php elseif($maintenance->status === 'cancelled'): ?>
                                <span class="badge bg-danger">Cancelled</span>
                                <?php elseif($maintenance->status === 'in_progress'): ?>
                                <span class="badge bg-warning text-dark">In Progress</span>
                                <?php else: ?>
                                <span class="badge bg-info text-dark">Scheduled</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="<?php echo e(route('admin.maintenance.edit', $maintenance)); ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    <?php if($maintenance->status !== 'completed'): ?>
                                    <form method="POST" action="<?php echo e(route('admin.maintenance.complete', $maintenance)); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button class="btn btn-success btn-sm">Complete</button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" action="<?php echo e(route('admin.maintenance.destroy', $maintenance)); ?>" class="d-inline" onsubmit="return confirm('Delete this maintenance record?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-outline-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No maintenance records found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <?php echo e($maintenances->links('pagination::bootstrap-5')); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/admin/maintenance/index.blade.php ENDPATH**/ ?>