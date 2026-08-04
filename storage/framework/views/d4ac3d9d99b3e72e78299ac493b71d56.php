<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>MuniResQ Driver</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <?php echo app('Illuminate\Foundation\Vite')([
    'resources/css/app.css',
    'resources/js/app.js'
    ]); ?>;

    <style>
        body {
            background: #071329;
            color: #eef4ff;
        }

        .content-area {
            min-height: 100vh;
            background: radial-gradient(circle at top left, rgba(59, 105, 255, 0.12), transparent 28%),
                linear-gradient(180deg, #071329 0%, #08172f 100%);
        }

        .sidebar-driver {
            width: 280px;
            min-height: 100vh;
            background: linear-gradient(180deg, #06243a 0%, #0b3658 100%);
            color: #f8fafc;
            display: none;
        }

        .sidebar-driver .brand,
        .sidebar-driver .driver-profile,
        .sidebar-driver .sidebar-nav,
        .sidebar-driver .mt-auto {
            border-color: rgba(255, 255, 255, 0.08);
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.16);
        }

        .driver-avatar {
            position: relative;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.16);
            display: grid;
            place-items: center;
            font-size: 1.8rem;
        }

        .status-dot {
            position: absolute;
            bottom: 4px;
            right: 4px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #06243a;
        }

        .status-dot.online {
            background: #20c997;
        }

        .status-dot.offline {
            background: #f4b942;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.85rem 0.95rem;
            margin-bottom: 0.35rem;
            border-radius: 0.9rem;
            color: rgba(255, 255, 255, 0.88);
            text-decoration: none;
            transition: background-color .2s ease, color .2s ease;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background-color: rgba(255, 255, 255, 0.12);
            color: #ffffff;
        }

        .mobile-nav-toggle {
            min-height: 46px;
            border-radius: 999px;
        }

        .card {
            background: rgba(7, 18, 38, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.5rem;
        }

        .card-body,
        .card-header,
        .card-footer {
            color: rgba(255, 255, 255, 0.92);
        }

        .card-header {
            background: rgba(11, 26, 49, 0.92);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .text-muted {
            color: rgba(255, 255, 255, 0.72) !important;
        }

        .table thead th,
        .table tbody td,
        .table tbody th {
            background: rgba(255, 255, 255, 0.04) !important;
            color: #eef4ff !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        .btn-outline-secondary {
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.5);
        }

        .btn-outline-secondary:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .btn-outline-primary {
            color: #ffffff;
            border-color: rgba(13, 110, 253, 0.7);
        }

        .btn-outline-primary:hover {
            background: rgba(13, 110, 253, 0.12);
        }

        .form-control,
        .form-select,
        .input-group-text,
        .form-check-input {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.14);
            color: #eef4ff;
        }

        .form-control:focus,
        .form-select:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(13, 110, 253, 0.9);
            color: #eef4ff;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.2);
        }

        .form-control::placeholder,
        .form-select {
            color: rgba(255, 255, 255, 0.65);
        }

        .form-check-label,
        .input-group-text,
        label,
        legend {
            color: rgba(255, 255, 255, 0.88);
        }

        .bg-light {
            background-color: rgba(255, 255, 255, 0.08) !important;
            color: #eef4ff !important;
        }

        .bg-white {
            background-color: rgba(255, 255, 255, 0.08) !important;
            color: #eef4ff !important;
        }

        @media (min-width: 992px) {
            .sidebar-driver {
                display: flex;
            }

            .mobile-nav-toggle {
                display: none;
            }
        }
    </style>
</head>

<body class="driver-layout">
    <?php
    use App\Models\Dispatch;

    $currentRoute = request()->route()?->getName() ?? '';

    $isActive = function(string $name) use ($currentRoute) {
    return str_contains($currentRoute, $name) ? 'active' : '';
    };

    $user = auth()->user();
    $driver = $user?->driver;

    $activeDispatch = null;
    $latestIncident = null;

    if ($driver) {
    $activeDispatch = \App\Models\Dispatch::where('driver_id', $driver->id)
    ->whereIn('status', [
    \App\Models\Dispatch::STATUS_ASSIGNED,
    \App\Models\Dispatch::STATUS_ACCEPTED,
    \App\Models\Dispatch::STATUS_EN_ROUTE,
    \App\Models\Dispatch::STATUS_ARRIVED,
    ])->latest()->first();

    $latestIncident = \App\Models\Incident::where('driver_id', $driver->id)->latest()->first();
    }

    $driverName = $user?->name ?? 'Driver';
    $driverBadge = $driver?->badge_id ?? null;
    $driverStatusLabel = $driver?->status ? ucfirst($driver->status) : 'Offline';
    $onlineClass = $driver?->status === 'available' ? 'online' : 'offline';
    ?>

    <div class="app-shell d-flex flex-column flex-lg-row">
        <aside id="driverSidebar" class="sidebar-driver flex-column d-none d-lg-flex">
            <div class="brand p-3 d-flex align-items-center gap-2">
                <div class="brand-icon d-flex align-items-center justify-content-center"><i class="bi bi-hospital text-white fs-4"></i></div>
                <div>
                    <div class="h6 mb-0 text-white">MuniResQ</div>
                    <small class="text-white-50">Driver Operations</small>
                </div>
            </div>

            <div class="driver-profile p-3 text-center">
                <div class="driver-avatar mx-auto mb-2"><i class="bi bi-person-fill"></i>
                    <span class="status-dot <?php echo e($onlineClass); ?>" title="<?php echo e($driverStatusLabel); ?>"></span>
                </div>
                <div class="driver-name text-white fw-semibold"><?php echo e($driverName); ?></div>
                <div class="driver-meta text-white-50 small">
                    <?php if($driverBadge): ?><span class="me-2">Badge: <?php echo e($driverBadge); ?></span><?php endif; ?>
                    <span class="badge bg-light text-dark ms-1"><?php echo e($driverStatusLabel); ?></span>
                </div>
            </div>

            <nav class="nav flex-column sidebar-nav p-3">
                <a href="<?php echo e(route('driver.dashboard')); ?>" class="sidebar-link <?php echo e($isActive('driver.dashboard')); ?>"><i class="bi bi-speedometer2 fs-4"></i><span class="label">Dashboard</span></a>
                <a href="<?php echo e(route('driver.navigation')); ?>" class="sidebar-link <?php echo e($isActive('driver.navigation')); ?>"><i class="bi bi-geo-alt-fill fs-4"></i><span class="label">Navigation</span></a>
                <a href="<?php echo e(route('driver.assignment')); ?>" class="sidebar-link <?php echo e($isActive('driver.assignment')); ?>"><i class="bi bi-list-check fs-4"></i><span class="label">My Assignment</span></a>
                <a href="<?php echo e(route('driver.report.create')); ?>" class="sidebar-link <?php echo e($isActive('driver.report.create')); ?>"><i class="bi bi-file-earmark-medical fs-4"></i><span class="label">Reports</span></a>
                <a href="<?php echo e(route('driver.history')); ?>" class="sidebar-link <?php echo e($isActive('driver.history')); ?>"><i class="bi bi-clock-history fs-4"></i><span class="label">Dispatch History</span></a>
                <a href="<?php echo e(route('driver.settings')); ?>" class="sidebar-link <?php echo e($isActive('driver.settings')); ?>"><i class="bi bi-gear fs-4"></i><span class="label">Settings</span></a>
                <a href="<?php echo e(route('profile.edit')); ?>" class="sidebar-link <?php echo e($isActive('profile.edit')); ?>"><i class="bi bi-person-circle fs-4"></i><span class="label">Profile</span></a>
            </nav>

            <div class="mt-auto p-3">
                <form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2 rounded-pill"><i class="bi bi-box-arrow-right"></i><span>Logout</span></button>
                </form>
            </div>
        </aside>

        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="driverOffcanvas" aria-labelledby="driverOffcanvasLabel">
            <div class="offcanvas-header border-bottom border-light-subtle bg-dark">
                <h5 class="offcanvas-title text-white" id="driverOffcanvasLabel">Driver Navigation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0">
                <div class="p-3 bg-dark text-white">
                    <div class="fw-semibold"><?php echo e($driverName); ?></div>
                    <div class="small text-white-50"><?php echo e($driverStatusLabel); ?></div>
                </div>
                <nav class="nav flex-column p-3">
                    <a href="<?php echo e(route('driver.dashboard')); ?>" class="sidebar-link <?php echo e($isActive('driver.dashboard')); ?>"><i class="bi bi-speedometer2 fs-4"></i><span class="label">Dashboard</span></a>
                    <a href="<?php echo e(route('driver.navigation')); ?>" class="sidebar-link <?php echo e($isActive('driver.navigation')); ?>"><i class="bi bi-geo-alt-fill fs-4"></i><span class="label">Navigation</span></a>
                    <a href="<?php echo e(route('driver.assignment')); ?>" class="sidebar-link <?php echo e($isActive('driver.assignment')); ?>"><i class="bi bi-list-check fs-4"></i><span class="label">My Assignment</span></a>
                    <a href="<?php echo e(route('driver.report.create')); ?>" class="sidebar-link <?php echo e($isActive('driver.report.create')); ?>"><i class="bi bi-file-earmark-medical fs-4"></i><span class="label">Reports</span></a>
                    <a href="<?php echo e(route('driver.history')); ?>" class="sidebar-link <?php echo e($isActive('driver.history')); ?>"><i class="bi bi-clock-history fs-4"></i><span class="label">Dispatch History</span></a>
                    <a href="<?php echo e(route('driver.settings')); ?>" class="sidebar-link <?php echo e($isActive('driver.settings')); ?>"><i class="bi bi-gear fs-4"></i><span class="label">Settings</span></a>
                    <a href="<?php echo e(route('profile.edit')); ?>" class="sidebar-link <?php echo e($isActive('profile.edit')); ?>"><i class="bi bi-person-circle fs-4"></i><span class="label">Profile</span></a>
                </nav>
            </div>
        </div>

        <main class="content-area flex-grow-1">
            <div class="container-fluid p-3 p-lg-4">
                <div class="d-flex justify-content-between align-items-center mb-3 d-lg-none">
                    <button class="btn btn-outline-primary mobile-nav-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#driverOffcanvas" aria-controls="driverOffcanvas">
                        <i class="bi bi-list me-2"></i>Menu
                    </button>
                    <div class="small text-muted">Emergency Dispatch</div>
                </div>
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>

</html><?php /**PATH C:\laragon\www\muniresq-project\resources\views/layouts/driver.blade.php ENDPATH**/ ?>