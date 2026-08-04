<?php $__env->startSection('content'); ?>
<?php
$driver = auth()->user()?->driver;
$user = $user ?? auth()->user();
?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <h2 class="h4 mb-4">Driver Profile</h2>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100 border-0 bg-light">
                    <div class="card-body">
                        <h5 class="card-title">Account Overview</h5>
                        <p class="text-muted mb-2">Badge ID</p>
                        <p class="fw-semibold"><?php echo e($driver?->badge_id ?? 'Pending'); ?></p>
                        <p class="text-muted mb-2">Full Name</p>
                        <p class="fw-semibold"><?php echo e($user?->name ?? 'N/A'); ?></p>
                        <p class="text-muted mb-2">Email</p>
                        <p class="fw-semibold"><?php echo e($user?->email ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100 border-0 bg-dark">
                    <div class="card-body">
                        <h5 class="card-title">Contact & Status</h5>
                        <p class="text-muted mb-2">Contact Number</p>
                        <p class="fw-semibold"><?php echo e($driver?->contact_number ?? 'N/A'); ?></p>
                        <p class="text-muted mb-2">Status</p>
                        <p class="fw-semibold"><?php echo e($driver?->status ?? 'available'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100 border-0 bg-dark">
                    <div class="card-body">
                        <h5 class="card-title">Assigned Vehicle</h5>
                        <p class="fw-semibold"><?php echo e($driver?->vehicle?->vehicle_name ?? 'Not assigned'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <h5 class="mb-3">Manage Account</h5>
        <div class="row g-4">
            <div class="col-lg-6">
                <?php echo $__env->make('profile.partials.update-profile-information-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <div class="col-lg-6">
                <?php echo $__env->make('profile.partials.update-password-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

        <div class="mt-4">
            <?php echo $__env->make('profile.partials.delete-user-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.driver', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/profile/edit.blade.php ENDPATH**/ ?>