<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MuniResQ Admin Panel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <?php echo app('Illuminate\Foundation\Vite')([
    'resources/css/app.css',
    'resources/js/app.js'
    ]); ?>
</head>

<?php
$adminRoute = function ($name) {
return Route::has($name) ? route($name) : '#';
};
?>

<body class="bg-light">

    <div class="container-fluid">

        <div class="row min-vh-100">

            <!-- SIDEBAR -->

            <div class="col-md-2 bg-dark text-white p-0">

                <div class="p-3 border-bottom">
                    <h4 class="mb-0">
                        🚑 MuniResQ
                    </h4>
                </div>

                <div class="list-group list-group-flush">

                    <a href="<?php echo e(route('admin.dashboard')); ?>"
                        class="list-group-item list-group-item-action">
                        📊 Dashboard
                    </a>

                    <a href="<?php echo e(route('admin.audit.logs')); ?>"
                        class="list-group-item list-group-item-action">
                        📜 Audit Logs
                    </a>

                    <a href="<?php echo e($adminRoute('admin.notifications.index')); ?>"
                        class="list-group-item list-group-item-action">

                        🔔 Notifications

                        <span class="badge bg-danger float-end" data-unread-badge>
                            <?php echo e($unreadNotifications ?? 0); ?>

                        </span>

                    </a>

                    <a href="<?php echo e(url('/admin/incidents')); ?>"
                        class="list-group-item list-group-item-action">
                        🚨 Incidents
                    </a>

                    <a href="<?php echo e($adminRoute('admin.dispatches.index')); ?>"
                        class="list-group-item list-group-item-action">
                        🚑 Dispatch Center
                    </a>

                    <a href="<?php echo e($adminRoute('admin.gps.monitoring')); ?>"
                        class="list-group-item list-group-item-action">
                        📍 GPS Monitoring
                    </a>

                    <a href="<?php echo e($adminRoute('admin.reports.index')); ?>"
                        class="list-group-item list-group-item-action">
                        📄 Incident Reports
                    </a>

                    <a href="<?php echo e($adminRoute('admin.reports.pdf.view')); ?>"
                        class="list-group-item list-group-item-action">
                        📑 PDF Reports
                    </a>

                    <a href="<?php echo e($adminRoute('admin.maintenance.index')); ?>"
                        class="list-group-item list-group-item-action">
                        🔧 Vehicle Maintenance
                    </a>

                    <a href="<?php echo e($adminRoute('admin.operations.center')); ?>"
                        class="list-group-item list-group-item-action">
                        🎯 Operations Center
                    </a>

                    <a href="<?php echo e($adminRoute('admin.reports.center')); ?>"
                        class="list-group-item list-group-item-action">
                        📈 Reports Center
                    </a>

                    <a href="<?php echo e($adminRoute('admin.reports.response-time')); ?>"
                        class="list-group-item list-group-item-action">
                        ⏱️ Response Time
                    </a>

                </div>

            </div>

            <!-- CONTENT -->

            <div class="col-md-10">

                <div class="p-4">

                    <?php echo $__env->yieldContent('content'); ?>

                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const badge = document.querySelector('[data-unread-badge]');

            if (!badge) {
                return;
            }

            const updateBadge = () => {
                fetch('<?php echo e($adminRoute("admin.notifications.unread-count")); ?>')
                    .then(response => response.json())
                    .then(data => {
                        badge.textContent = data.unread_count ?? 0;
                    })
                    .catch(() => {
                        badge.textContent = '0';
                    });
            };

            updateBadge();
            setInterval(updateBadge, 15000);
        });
    </script>

</body>

</html><?php /**PATH C:\laragon\www\muniresq-project\resources\views/layouts/admin.blade.php ENDPATH**/ ?>