

<?php $__env->startSection('content'); ?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-danger">Incident Command</h2>
        <p class="text-muted mb-0">Track active incidents and dispatch the nearest ambulance resources.</p>
    </div>
    <a href="<?php echo e(route('admin.incidents.create')); ?>" class="btn btn-danger">Create Incident</a>
</div>

<?php if(session('success')): ?>
<div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Incident No</th>
                        <th>Reporter</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $incidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="fw-semibold"><?php echo e($incident->incident_number); ?></td>
                        <td><?php echo e($incident->reporter_name); ?></td>
                        <td><?php echo e($incident->incident_type); ?></td>
                        <td><?php echo e($incident->location); ?></td>

                        <td>
                            <?php if($incident->priority == 'Critical'): ?>
                            <span class="badge bg-danger">
                                🔴 Critical
                            </span>
                            <?php elseif($incident->priority == 'High'): ?>
                            <span class="badge bg-warning text-dark">
                                🟠 High
                            </span>
                            <?php elseif($incident->priority == 'Medium'): ?>
                            <span class="badge bg-info text-dark">
                                🟡 Medium
                            </span>
                            <?php else: ?>
                            <span class="badge bg-success">
                                🟢 Low
                            </span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-primary-subtle text-primary"><?php echo e(ucfirst($incident->status)); ?></span></td>
                        <td>
                            <a href="<?php echo e(route('admin.incidents.dispatch.form', $incident)); ?>" class="btn btn-sm btn-outline-danger">Dispatch</a>
                        </td>

                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No incidents recorded yet.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/admin/incidents/index.blade.php ENDPATH**/ ?>