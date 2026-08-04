

<?php $__env->startSection('content'); ?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <div>
        <h2 class="section-heading mb-1">Dispatch Center</h2>
        <p class="section-excerpt mb-0">Assign drivers and ambulances to active incidents with clarity and speed.</p>
    </div>
</div>

<?php if(session('success')): ?>
<div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<div class="row g-4">
    <?php $__empty_1 = true; $__currentLoopData = $incidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 admin-card">
            <div class="card-header bg-danger text-white border-0 rounded-top-4">
                <div class="d-flex justify-content-between align-items-center">
                    <strong><?php echo e($incident->incident_number ?? 'Incident'); ?></strong>
                    <span class="badge bg-danger text-white"><?php echo e(ucfirst($incident->status)); ?></span>
                </div>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Location:</strong> <?php echo e($incident->location); ?></p>
                <p class="mb-3"><strong>Type:</strong> <?php echo e($incident->incident_type); ?></p>

                <form action="<?php echo e(route('admin.dispatches.assign', $incident)); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="mb-3">
                        <label class="form-label">Driver</label>
                        <select name="driver_id" class="form-select" required>
                            <option value="">Select Driver</option>
                            <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($driver->id); ?>">
                                <?php echo e($driver->user->name); ?>

                                (<?php echo e($driver->badge_id); ?>)
                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ambulance</label>
                        <select name="ambulance_id" class="form-select" required>
                            <option value="">Select Ambulance</option>
                            <?php $__currentLoopData = $ambulances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ambulance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ambulance->id); ?>">
                                <?php echo e($ambulance->vehicle_name); ?>

                                (<?php echo e($ambulance->plate_number); ?>)
                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-danger w-100">
                        Dispatch Vehicle
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="col-12">
        <div class="alert alert-light text-muted">No dispatch assignments pending.</div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/admin/dispatches/index.blade.php ENDPATH**/ ?>