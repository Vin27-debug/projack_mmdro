

<?php $__env->startSection('content'); ?>
<div class="card shadow-sm border-0 rounded-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">Live Command Center</h2>
            <p class="text-muted mb-0">Real-time emergency operations overview with live geospatial intelligence.</p>
        </div>
        <div class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">
            <i class="bi bi-arrow-repeat"></i> Auto-refresh every 10 seconds
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm bg-danger-subtle text-danger h-100">
                <div class="card-body">
                    <div class="small text-uppercase fw-semibold">Live Incidents</div>
                    <div class="display-6 fw-bold mt-2"><?php echo e($stats['live_incidents']); ?></div>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="card border-0 shadow-sm bg-primary-subtle text-primary h-100">
                <div class="card-body">
                    <div class="small text-uppercase fw-semibold">Active Ambulances</div>
                    <div class="display-6 fw-bold mt-2"><?php echo e($stats['active_ambulances']); ?></div>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="card border-0 shadow-sm bg-warning-subtle text-warning h-100">
                <div class="card-body">
                    <div class="small text-uppercase fw-semibold">Panic Alerts</div>
                    <div class="display-6 fw-bold mt-2"><?php echo e($stats['panic_alerts']); ?></div>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="card border-0 shadow-sm bg-dark-subtle text-dark h-100">
                <div class="card-body">
                    <div class="small text-uppercase fw-semibold">Hijack Alerts</div>
                    <div class="display-6 fw-bold mt-2"><?php echo e($stats['hijack_alerts']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-9 col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-map"></i> Live Tactical Map</span>
                    <span class="small opacity-75">Fullscreen-ready Leaflet view</span>
                </div>
                <div class="card-body p-0">
                    <div id="operationsMap" style="height: 72vh; min-height: 520px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-broadcast"></i> Active Dispatches
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $activeDispatches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dispatch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border rounded p-3 mb-2">
                        <div class="fw-semibold"><?php echo e($dispatch->incident?->incident_number ?? 'Unassigned incident'); ?></div>
                        <div class="small text-muted">Vehicle: <?php echo e($dispatch->vehicle?->plate_number ?? 'N/A'); ?></div>
                        <div class="small text-muted">Driver: <?php echo e($dispatch->driver?->user?->name ?? 'Unassigned'); ?></div>
                        <div class="mt-2">
                            <span class="badge bg-primary"><?php echo e(strtoupper(str_replace('_', ' ', $dispatch->status))); ?></span>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-muted small">No active dispatches.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-truck"></i> Active Vehicles
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $vehicles->whereIn('status', ['available', 'on_duty']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <span><?php echo e($vehicle->vehicle_name); ?></span>
                        <span class="badge bg-success"><?php echo e(ucfirst($vehicle->status)); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-muted small">No active vehicles.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-exclamation-triangle"></i> Pending Incidents
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $incidents->where('status', 'pending'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border rounded p-2 mb-2">
                        <div class="fw-semibold"><?php echo e($incident->incident_number); ?></div>
                        <div class="small text-muted"><?php echo e($incident->location); ?></div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-muted small">No pending incidents.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <i class="bi bi-shield-exclamation"></i> Panic Alerts
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $panicAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border rounded p-2 mb-2">
                        <div class="fw-semibold"><?php echo e($alert->driver?->user?->name ?? 'Driver'); ?></div>
                        <div class="small text-muted"><?php echo e($alert->triggered_at?->diffForHumans() ?? 'Recently triggered'); ?></div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-muted small">No panic alerts.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const map = L.map('operationsMap').setView([15.4866, 120.9675], 12);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const mapData = <?php echo json_encode($mapData, 15, 512) ?>;

    const layerStyles = {
        incident: {
            color: '#dc3545',
            radius: 9,
            fillColor: '#dc3545'
        },
        panic: {
            color: '#fd7e14',
            radius: 11,
            fillColor: '#fd7e14'
        },
        hijack: {
            color: '#000000',
            radius: 11,
            fillColor: '#000000'
        },
        vehicle: {
            color: '#0d6efd',
            radius: 7,
            fillColor: '#0d6efd'
        },
    };

    const addMarker = (item, type) => {
        if (!item.latitude || !item.longitude) return;
        const style = layerStyles[type] || layerStyles.vehicle;
        if (type === 'vehicle') {
            L.marker([item.latitude, item.longitude])
                .addTo(map)
                .bindPopup(`<div class="fw-semibold">${item.title}</div><div class="small">Plate: ${item.plate_number || 'N/A'}</div><div class="small">Status: ${item.status || 'Unknown'}</div>`);
            return;
        }

        L.circleMarker([item.latitude, item.longitude], {
            radius: style.radius,
            color: style.color,
            fillColor: style.fillColor,
            fillOpacity: 0.85,
            weight: 2
        }).addTo(map).bindPopup(`<div class="fw-semibold">${item.title}</div><div class="small">${item.location || item.status || 'Live event'}</div>`);
    };

    mapData.incidents.forEach(item => addMarker(item, 'incident'));
    mapData.panicAlerts.forEach(item => addMarker(item, 'panic'));
    mapData.hijackAlerts.forEach(item => addMarker(item, 'hijack'));
    mapData.vehicles.forEach(item => addMarker(item, 'vehicle'));

    setInterval(() => {
        window.location.reload();
    }, 10000);
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/admin/operations-center.blade.php ENDPATH**/ ?>