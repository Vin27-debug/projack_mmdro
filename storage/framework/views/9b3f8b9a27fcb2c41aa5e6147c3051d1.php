

<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">📝 Incident Report</h2>
            <p class="text-muted mb-0">
                Complete the emergency response report.
            </p>
        </div>
    </div>

    <div class="card shadow-sm border-0">

        <div class="card-body p-4">

            <?php if(!$incident): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-exclamation-circle fs-1 mb-3"></i>
                <h5 class="fw-semibold">No incident available</h5>
                <p class="mb-4">There is no completed incident ready for reporting at the moment.</p>
                <a href="<?php echo e(route('driver.dashboard')); ?>" class="btn btn-primary">Back to Dashboard</a>
            </div>
            <?php else: ?>
            <form method="POST"
                action="<?php echo e(route('driver.report.store', $incident)); ?>">

                <?php echo csrf_field(); ?>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Incident Number</label>
                        <input type="text" class="form-control" value="<?php echo e($incident->incident_number); ?>" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Incident Type</label>
                        <input type="text" class="form-control" value="<?php echo e($incident->incident_type); ?>" readonly>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" value="<?php echo e($incident->location); ?>" readonly>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Summary of Response</label>
                        <textarea name="summary" rows="3" class="form-control"><?php echo e(old('summary')); ?></textarea>
                        <?php $__errorArgs = ['summary'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger mt-1"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Actions Taken</label>
                        <textarea name="actions_taken" rows="3" class="form-control"><?php echo e(old('actions_taken')); ?></textarea>
                        <?php $__errorArgs = ['actions_taken'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger mt-1"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Casualties</label>
                        <input type="text" name="casualties" value="<?php echo e(old('casualties')); ?>" class="form-control">
                        <?php $__errorArgs = ['casualties'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger mt-1"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" value="<?php echo e(old('remarks')); ?>" class="form-control">
                        <?php $__errorArgs = ['remarks'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger mt-1"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                </div>

                <hr>

                <div class="text-end">
                    <a href="<?php echo e(route('driver.dashboard')); ?>" class="btn btn-outline-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Report</button>
                </div>

            </form>
            <?php endif; ?>

        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.driver', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/driver/reports/create.blade.php ENDPATH**/ ?>