

<?php $__env->startSection('content'); ?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-danger">Reports Center</h2>
        <p class="text-muted mb-0">Review submitted incident reports and approve them for closure.</p>
    </div>
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
                        <th>ID</th>
                        <th>Incident Number</th>
                        <th>Driver</th>
                        <th>Summary</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>#<?php echo e($report->id); ?></td>
                        <td><?php echo e($report->incident?->incident_number ?? 'N/A'); ?></td>
                        <td><?php echo e($report->driver?->user?->name ?? 'N/A'); ?></td>
                        <td><?php echo e($report->summary); ?></td>
                        <td>
                            <?php if($report->status == 'pending'): ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                            <?php elseif($report->status == 'approved'): ?>
                            <span class="badge bg-success">Approved</span>
                            <?php elseif($report->status == 'rejected'): ?>
                            <span class="badge bg-danger">Rejected</span>
                            <?php else: ?>
                            <span class="badge bg-secondary"><?php echo e($report->status); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($report->submitted_at); ?></td>
                        <td>
                            <?php if($report->status == 'pending'): ?>
                            <form method="POST" action="<?php echo e(route('admin.reports.approve', $report)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No reports found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/admin/reports/index.blade.php ENDPATH**/ ?>