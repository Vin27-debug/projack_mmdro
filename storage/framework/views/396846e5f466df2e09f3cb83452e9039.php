

<?php $__env->startSection('content'); ?>

<div class="container">

    <h2 class="mb-4">
        Vehicle Utilization Report
    </h2>

    <table class="table table-bordered">

        <thead>
            <tr>
                <th>Plate Number</th>
                <th>Vehicle</th>
                <th>Status</th>
                <th>Vehicle Usage Count</th>
                <th>Total Dispatches</th>
                <th>Downtime</th>
                <th>Maintenance Count</th>
                <th>Availability Rate</th>
            </tr>
        </thead>

        <tbody>

            <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($vehicle->plate_number); ?></td>
                <td><?php echo e($vehicle->vehicle_name); ?></td>
                <td><?php echo e(strtoupper($vehicle->status)); ?></td>
                <td><?php echo e($vehicle->usage_count); ?></td>
                <td><?php echo e($vehicle->total_dispatches); ?></td>
                <td><?php echo e($vehicle->downtime); ?> day(s)</td>
                <td><?php echo e($vehicle->maintenance_count); ?></td>
                <td><?php echo e($vehicle->availability_rate); ?>%</td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </tbody>

    </table>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/admin/vehicle-utilization.blade.php ENDPATH**/ ?>