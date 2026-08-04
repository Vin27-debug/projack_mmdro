

<?php $__env->startSection('content'); ?>

<div class="page-header">
    <div>
        <div class="small text-uppercase text-white-50 mb-2">Data Protection</div>
        <h1 class="page-title">Database Backups</h1>
        <p class="page-subtitle mb-0">Create backups and review recent restore history.</p>
    </div>
    <a href="<?php echo e(route('superadmin.dashboard')); ?>" class="btn btn-outline-light page-back-button">
        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
    </a>
</div>

<?php if(session('success')): ?>
<div class="alert alert-success">
    <?php echo e(session('success')); ?>

</div>
<?php endif; ?>

<div class="card admin-card border-0 shadow-sm p-4 mb-4">
    <form method="POST" action="<?php echo e(route('backups.create')); ?>">
        <?php echo csrf_field(); ?>
        <button class="btn btn-primary">Backup Now</button>
    </form>
</div>

<div class="card admin-card border-0 shadow-sm p-4 mb-4">
    <h4 class="text-white mb-3">Backup History</h4>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Filename</th>
                    <th>Status</th>
                    <th>Message</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($log->type); ?></td>
                    <td><?php echo e($log->filename); ?></td>
                    <td><?php echo e($log->status); ?></td>
                    <td><?php echo e($log->message); ?></td>
                    <td><?php echo e($log->created_at->format('Y-m-d H:i')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <hr>

        <table class="table">

            <thead>
                <tr>
                    <th>Backup File</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                <tr>

                    <td>
                        <?php echo e(basename($file)); ?>

                    </td>

                    <td>

                        <a
                            href="<?php echo e(route('backups.download', basename($file))); ?>"
                            class="btn btn-success btn-sm">
                            Download
                        </a>

                        <form
                            method="POST"
                            action="<?php echo e(route('backups.restore')); ?>"
                            style="display:inline;">

                            <?php echo csrf_field(); ?>

                            <input
                                type="hidden"
                                name="backup_file"
                                value="<?php echo e(basename($file)); ?>">

                            <button
                                class="btn btn-warning btn-sm">
                                Restore
                            </button>

                        </form>

                    </td>

                </tr>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </tbody>

        </table>

        <?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.superadmin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/superadmin/backups/index.blade.php ENDPATH**/ ?>