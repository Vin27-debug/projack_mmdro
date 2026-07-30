<h1>Add Ambulance</h1>

<?php if($errors->any()): ?>
<ul>
    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <li><?php echo e($error); ?></li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php endif; ?>

<form method="POST"
    action="<?php echo e(route('ambulances.store')); ?>">

    <?php echo csrf_field(); ?>

    <p>
        Plate Number
    </p>

    <input
        type="text"
        name="plate_number"
        placeholder="ABC-1234">

    <br><br>

    <p>
        Vehicle Name
    </p>

    <input
        type="text"
        name="vehicle_name"
        placeholder="Toyota HiAce">

    <br><br>

    <p>
        Vehicle Type
    </p>

    <select name="vehicle_type">

        <option value="ambulance">
            Ambulance
        </option>

        <option value="rescue_van">
            Rescue Van
        </option>

        <option value="fire_truck">
            Fire Truck
        </option>

    </select>

    <br><br>

    <button type="submit">
        Save Ambulance
    </button>

</form><?php /**PATH C:\laragon\www\muniresq-project\resources\views/superadmin/ambulances/create.blade.php ENDPATH**/ ?>