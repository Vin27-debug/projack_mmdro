

<?php $__env->startSection('content'); ?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-danger">Vehicle Fleet</h2>
        <p class="text-muted mb-0">Manage ambulance availability, maintenance status, and fleet readiness.</p>
    </div>
    <a href="<?php echo e(route('ambulances.create')); ?>" class="btn btn-danger">Add Ambulance</a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Plate Number</th>
                        <th>Vehicle Name</th>
                        <th>Vehicle Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $ambulances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ambulance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>#<?php echo e($ambulance->id); ?></td>
                        <td><?php echo e($ambulance->plate_number); ?></td>
                        <td><?php echo e($ambulance->vehicle_name); ?></td>
                        <td><?php echo e($ambulance->vehicle_type); ?></td>
                        <td>
                            <?php if($ambulance->status == 'available'): ?>
                            <span class="badge bg-success">Available</span>
                            <?php elseif($ambulance->status == 'maintenance'): ?>
                            <span class="badge bg-warning text-dark">Maintenance</span>
                            <?php else: ?>
                            <span class="badge bg-secondary"><?php echo e(ucfirst($ambulance->status)); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="<?php echo e(route('ambulances.edit', $ambulance->id)); ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="<?php echo e(route('ambulances.destroy', $ambulance->id)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this ambulance?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No ambulances found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/superadmin/ambulances/index.blade.php ENDPATH**/ ?>