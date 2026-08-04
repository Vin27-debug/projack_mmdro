

<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-1">Driver Portal</p>
        <h2 class="fw-bold text-white mb-0">My Current Assignment</h2>
    </div>
</div>

<?php if(!$incident || !$dispatch): ?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body text-center py-5">
        <h4 class="fw-bold text-white">
            No Active Assignment
        </h4>
        <p class="text-muted mb-0">
            You currently have no dispatch assigned.
        </p>
    </div>
</div>

<?php else: ?>

<div class="row g-4">

    <div class="col-lg-8">

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-danger text-white rounded-top-4">
                <h5 class="mb-0">
                    Incident Information
                </h5>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">
                            Incident Number
                        </label>

                        <div class="fw-bold">
                            <?php echo e($incident->incident_number); ?>

                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">
                            Incident Type
                        </label>

                        <div class="fw-bold">
                            <?php echo e($incident->incident_type); ?>

                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="text-muted small">
                            Location
                        </label>

                        <div class="fw-bold">
                            <?php echo e($incident->location); ?>

                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="text-muted small">
                            Incident Status
                        </label>
                        <div>
                            <span class="badge bg-secondary">
                                <?php echo e(ucfirst(str_replace('_', ' ', $incident->status))); ?>

                            </span>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="text-muted small">
                            Dispatch Status
                        </label>
                        <div>
                            <?php
                            $dispatchStatus = $dispatch->status;
                            $badgeClass = match ($dispatchStatus) {
                            \App\Models\Dispatch::STATUS_PENDING => 'bg-warning text-dark',
                            \App\Models\Dispatch::STATUS_ASSIGNED => 'bg-primary',
                            \App\Models\Dispatch::STATUS_ACCEPTED => 'bg-info text-dark',
                            \App\Models\Dispatch::STATUS_EN_ROUTE => 'bg-warning text-dark',
                            \App\Models\Dispatch::STATUS_ARRIVED => 'bg-success',
                            \App\Models\Dispatch::STATUS_COMPLETED => 'bg-dark',
                            \App\Models\Dispatch::STATUS_CANCELLED => 'bg-danger',
                            default => 'bg-secondary',
                            };
                            ?>
                            <span class="badge <?php echo e($badgeClass); ?>">
                                <?php echo e(ucfirst(str_replace('_', ' ', $dispatchStatus))); ?>

                            </span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-4">

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-dark text-white rounded-top-4">
                <h5 class="mb-0">
                    Assigned Vehicle
                </h5>
            </div>

            <div class="card-body">

                <div class="mb-3">
                    <label class="text-muted small">
                        Plate Number
                    </label>

                    <div class="fw-bold">
                        <?php echo e($dispatch->vehicle?->plate_number ?? $incident->ambulance?->plate_number); ?>

                    </div>
                </div>

                <div>
                    <label class="text-muted small">
                        Vehicle
                    </label>

                    <div class="fw-bold">
                        <?php echo e($dispatch->vehicle?->vehicle_name ?? $incident->ambulance?->vehicle_name); ?>

                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

<div class="card border-0 shadow-sm rounded-4 mt-4">

    <div class="card-body">

        <h5 class="fw-bold mb-4">
            Assignment Actions
        </h5>

        <?php if(in_array($dispatch->status, [\App\Models\Dispatch::STATUS_PENDING, \App\Models\Dispatch::STATUS_ASSIGNED], true)): ?>

        <form method="POST"
            action="<?php echo e(route('driver.dispatch.accept', $dispatch)); ?>"
            class="d-inline">
            <?php echo csrf_field(); ?>

            <button class="btn btn-success">
                Accept Assignment
            </button>
        </form>

        <form method="POST"
            action="<?php echo e(route('driver.dispatch.decline', $dispatch)); ?>"
            class="d-inline ms-2">
            <?php echo csrf_field(); ?>

            <button class="btn btn-danger">
                Decline Assignment
            </button>
        </form>

        <?php elseif($dispatch->status === \App\Models\Dispatch::STATUS_ACCEPTED): ?>

        <form method="POST"
            action="<?php echo e(route('driver.incidents.en-route', $incident)); ?>"
            class="d-inline">
            <?php echo csrf_field(); ?>

            <button class="btn btn-primary">
                Mark En Route
            </button>
        </form>

        <?php elseif($dispatch->status === \App\Models\Dispatch::STATUS_EN_ROUTE): ?>

        <form method="POST"
            action="<?php echo e(route('driver.incidents.arrived', $incident)); ?>"
            class="d-inline">
            <?php echo csrf_field(); ?>

            <button class="btn btn-warning">
                Mark On Scene
            </button>
        </form>

        <?php elseif($dispatch->status === \App\Models\Dispatch::STATUS_ARRIVED): ?>

        <form method="POST"
            action="<?php echo e(route('driver.incidents.completed', $incident)); ?>"
            class="d-inline">
            <?php echo csrf_field(); ?>

            <button class="btn btn-success">
                Mark Completed
            </button>
        </form>

        <?php elseif($dispatch->status === \App\Models\Dispatch::STATUS_COMPLETED): ?>

        <a href="<?php echo e(route('driver.report.create', $incident)); ?>"
            class="btn btn-danger">
            Submit Final Report
        </a>

        <?php endif; ?>

    </div>

</div>

<?php endif; ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.driver', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/driver/assignment/index.blade.php ENDPATH**/ ?>