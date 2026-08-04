

<?php $__env->startSection('content'); ?>

<div class="container-fluid">

    <?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="section-heading mb-1">Notifications</h2>
            <p class="section-excerpt mb-0">You have <?php echo e($unreadNotifications ?? 0); ?> unread messages.</p>
        </div>

        <form method="POST" action="<?php echo e(route('admin.notifications.read-all')); ?>" class="mb-0">
            <?php echo csrf_field(); ?>
            <button class="btn btn-success">Mark All Read</button>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4 admin-card">

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <tr>

                            <td>
                                <?php echo e($notification->title); ?>

                            </td>

                            <td>
                                <?php echo e($notification->message); ?>

                            </td>

                            <td>

                                <?php if($notification->is_read): ?>

                                <span class="badge bg-success">
                                    Read
                                </span>

                                <?php else: ?>

                                <span class="badge bg-danger">
                                    Unread
                                </span>

                                <?php endif; ?>

                            </td>

                            <td>
                                <?php echo e($notification->created_at); ?>

                            </td>

                            <td>
                                <?php if(!$notification->is_read): ?>
                                <form method="POST" action="<?php echo e(route('admin.notifications.read', $notification)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button class="btn btn-sm btn-outline-primary">Mark Read</button>
                                </form>
                                <?php else: ?>
                                <span class="text-muted">Done</span>
                                <?php endif; ?>
                            </td>

                        </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                        <tr>

                            <td colspan="5">
                                No Notifications
                            </td>

                        </tr>

                        <?php endif; ?>

                    </tbody>

                </table>
            </div>
        </div>

    </div>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/admin/notifications/index.blade.php ENDPATH**/ ?>