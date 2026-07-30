

<?php $__env->startSection('content'); ?>

<h1>Dispatch Incident</h1>

<p>
    Incident:
    <?php echo e($incident->incident_number); ?>

</p>

<?php if($nearestDriver || $nearestAmbulance): ?>

<div class="card border-success shadow-sm mb-4">

    <div class="card-header bg-success text-white">
        ⭐ Nearest Available Resource
    </div>

    <div class="card-body">

        <?php if($nearestDriver): ?>

        <h5 class="text-success">
            🚑 Recommended Driver
        </h5>

        <p class="mb-1">
            <strong>
                <?php echo e($nearestDriver->user->name ?? $nearestDriver->badge_id); ?>

            </strong>
        </p>

        <span class="badge bg-primary">
            <?php echo e(round($nearestDistance,2)); ?> KM Away
        </span>

        <?php
        $eta = max(1, ceil(($nearestDistance / 40) * 60));
        ?>

        <span class="badge bg-warning text-dark">
            ETA <?php echo e($eta); ?> mins
        </span>

        <?php endif; ?>

        <hr>

        <?php if($nearestAmbulance): ?>

        <h5 class="text-danger">
            🚐 Recommended Ambulance
        </h5>

        <p class="mb-1">

            <strong>
                <?php echo e($nearestAmbulance->plate_number); ?>

            </strong>

            <br>

            <?php echo e($nearestAmbulance->vehicle_name); ?>


        </p>

        <span class="badge bg-success">

            <?php echo e(round($nearestAmbulanceDistance,2)); ?> KM Away

        </span>

        <?php
        $etaVehicle = max(1, ceil(($nearestAmbulanceDistance / 40) * 60));
        ?>

        <span class="badge bg-warning text-dark">

            ETA <?php echo e($etaVehicle); ?> mins

        </span>

        <span class="badge bg-danger">

            ⭐ Recommended

        </span>

        <?php endif; ?>

    </div>

</div>

<?php endif; ?>

<form method="POST"
    action="<?php echo e(route('admin.incidents.dispatch', $incident)); ?>">

    <?php echo csrf_field(); ?>

    <div class="mb-3">

        <label>Driver</label>

        <select name="driver_id"
            class="form-control"
            <?php if($drivers->isEmpty()): ?> disabled <?php endif; ?>>

            <?php if($drivers->isEmpty()): ?>
            <option value="">No available drivers</option>
            <?php else: ?>
            <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <option
                value="<?php echo e($driver->id); ?>"
                <?php echo e(isset($nearestDriver) && $nearestDriver?->id == $driver->id ? 'selected' : ''); ?>>
                <?php if(isset($nearestDriver) && $nearestDriver?->id == $driver->id): ?>

                ⭐ <?php echo e($driver->badge_id); ?>


                <?php else: ?>

                <?php echo e($driver->badge_id); ?>


                <?php endif; ?>
            </option>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

        </select>

    </div>

    <div class="mb-3">

        <label>Ambulance</label>

        <select name="vehicle_id"
            class="form-control"
            <?php if($vehicles->isEmpty()): ?> disabled <?php endif; ?>>

            <?php if($vehicles->isEmpty()): ?>
            <option value="">No available ambulances</option>
            <?php else: ?>
            <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <option value="<?php echo e($vehicle->id); ?>"
                <?php echo e(isset($nearestAmbulance) && $nearestAmbulance?->id == $vehicle->id ? 'selected' : ''); ?>>
                <?php if(isset($nearestAmbulance) && $nearestAmbulance?->id == $vehicle->id): ?>

                ⭐ <?php echo e($vehicle->plate_number); ?> - <?php echo e($vehicle->vehicle_name); ?>


                <?php else: ?>

                <?php echo e($vehicle->plate_number); ?> - <?php echo e($vehicle->vehicle_name); ?>


                <?php endif; ?>
            </option>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>

        </select>

    </div>

    <button class="btn btn-primary" <?php if($drivers->isEmpty() || $vehicles->isEmpty()): ?> disabled <?php endif; ?>>
        Dispatch Incident
    </button>

</form>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/admin/incidents/dispatch.blade.php ENDPATH**/ ?>