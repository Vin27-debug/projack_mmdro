<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MuniResQ Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?php echo e(route('superadmin.dashboard')); ?>">MuniResQ Super Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#superadminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('superadmin.dashboard') ? 'active' : ''); ?>"
                        href="<?php echo e(route('superadmin.dashboard')); ?>">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('superadmin.drivers') ? 'active' : ''); ?>"
                        href="<?php echo e(route('superadmin.drivers')); ?>">
                        Drivers
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('ambulances.*') ? 'active' : ''); ?>"
                        href="<?php echo e(route('ambulances.index')); ?>">
                        Ambulances
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('backups.*') ? 'active' : ''); ?>"
                        href="<?php echo e(route('backups.index')); ?>">
                        Backup & Restore
                    </a>
                </li>

                <li class="nav-item">
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-link nav-link">
                            Logout
                        </button>
                    </form>
                </li>

            </ul>
        </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php echo $__env->yieldContent('content'); ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html><?php /**PATH C:\laragon\www\muniresq-project\resources\views/layouts/superadmin.blade.php ENDPATH**/ ?>