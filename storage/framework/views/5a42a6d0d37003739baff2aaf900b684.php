<h1>Approved Drivers</h1>

<table border="1" cellpadding="10">

    <tr>
        <th>ID</th>
        <th>Badge</th>
        <th>Name</th>
        <th>Email</th>
        <th>License</th>
    </tr>

    <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

    <tr>
        <td><?php echo e($driver->id); ?></td>
        <td><?php echo e($driver->badge_id); ?></td>
        <td><?php echo e($driver->user->name); ?></td>
        <td><?php echo e($driver->user->email); ?></td>
        <td><?php echo e($driver->license_number); ?></td>
    </tr>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</table><?php /**PATH C:\laragon\www\muniresq-project\resources\views/superadmin/drivers/index.blade.php ENDPATH**/ ?>