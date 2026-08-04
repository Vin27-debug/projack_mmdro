

<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    <h2 class="section-heading mb-4">
        Audit Logs
    </h2>

    <div class="card border-0 shadow-sm rounded-4 admin-card">

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr>
                            <td><?php echo e($log->id); ?></td>
                            <td><?php echo e($log->action); ?></td>
                            <td><?php echo e($log->module); ?></td>
                            <td><?php echo e($log->description); ?></td>
                            <td><?php echo e($log->ip_address); ?></td>
                            <td><?php echo e($log->created_at); ?></td>
                        </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <tr>
                            <td colspan="6" class="text-center">
                                No Audit Logs Found
                            </td>
                        </tr>

                        <?php endif; ?>

                    </tbody>

                </table>
            </div>

            <?php echo e($logs->links()); ?>


        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/admin/audit-logs/index.blade.php ENDPATH**/ ?>