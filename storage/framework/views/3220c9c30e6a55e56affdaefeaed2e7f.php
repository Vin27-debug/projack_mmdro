

<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="section-heading mb-1">GPS Monitoring</h2>
        <p class="section-excerpt mb-0">
            Track ambulances and assess route progress in real time.
        </p>
    </div>
</div>

<div class="row g-4 mb-4">

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 admin-card">
            <div class="card-body">
                <small class="text-muted">Driver Status</small>
                <?php if($incident?->status == 'dispatched'): ?>
                <span class="badge bg-primary fs-6">DISPATCHED</span>
                <?php elseif($incident?->status == 'on_scene'): ?>
                <span class="badge bg-warning text-dark fs-6">ON SCENE</span>
                <?php elseif($incident?->status == 'completed'): ?>
                <span class="badge bg-success fs-6">COMPLETED</span>
                <?php elseif($incident?->status == 'pending'): ?>
                <span class="badge bg-secondary fs-6">PENDING</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 admin-card">
            <div class="card-body">
                <small class="text-muted">Vehicle Status</small>
                <h4 class="fw-bold">
                    <?php echo e($incident?->ambulance?->status ?? 'N/A'); ?>

                </h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 admin-card">
            <div class="card-body">
                <small class="text-muted">Incident Status</small>
                <h4 class="fw-bold">
                    <?php echo e(strtoupper($incident?->status ?? 'N/A')); ?>

                </h4>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 admin-card">
            <div class="card-body">
                <small class="text-muted">ETA</small>
                <h4 class="fw-bold" id="eta">
                    Calculating...
                </h4>
                <small class="text-white-50" id="distance">
                    Distance: Calculating...
                </small>
            </div>
        </div>
    </div>

</div>

<div class="card border-0 shadow-sm rounded-4 mb-4 admin-card">
    <div class="card-body">
        <h5 class="fw-bold mb-3">Active Incident</h5>

        <div class="row">

            <div class="col-md-6 mb-3">
                <strong>Incident:</strong>
                <?php echo e($incident?->incident_number ?? 'N/A'); ?>

            </div>

            <div class="col-md-6 mb-3">
                <strong>Location:</strong>
                <?php echo e($incident?->location ?? 'N/A'); ?>

            </div>

            <div class="col-md-6">
                <strong>Status:</strong>

                <?php if($incident?->status == 'dispatched'): ?>
                <span class="badge bg-primary">Dispatched</span>
                <?php elseif($incident?->status == 'on_scene'): ?>
                <span class="badge bg-warning text-dark">On Scene</span>
                <?php elseif($incident?->status == 'completed'): ?>
                <span class="badge bg-success">Completed</span>
                <?php elseif($incident?->status == 'pending'): ?>
                <span class="badge bg-secondary">Pending</span>
                <?php else: ?>
                <span class="badge bg-secondary text-white">
                    <?php echo e(ucfirst($incident?->status ?? 'N/A')); ?>

                </span>
                <?php endif; ?>
            </div>

            <div class="col-md-6 mb-3">
                <strong>Driver:</strong>
                <?php echo e($incident?->driver?->user?->name ?? 'Not Assigned'); ?>

            </div>

            <div class="col-md-6">
                <strong>Vehicle:</strong>
                <?php echo e($incident?->ambulance?->vehicle_name ?? 'Not Assigned'); ?>

            </div>

        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div id="map" style="height:650px;"></div>
    </div>
</div>

<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">

<link rel="stylesheet"
    href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />

<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>

<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<script>
    const map = L.map('map').setView(
        [15.421486, 120.842827],
        13
    );

    L.tileLayer(
        'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }
    ).addTo(map);

    let markers = {};
    let routingControl = null;

    const incidentLat =
        Number('<?php echo e($incident?->latitude ?? 15.5000); ?>');

    const incidentLng =
        Number('<?php echo e($incident?->longitude ?? 120.8500); ?>');

    const incidentMarker =
        L.marker([incidentLat, incidentLng])
        .addTo(map)
        .bindPopup(
            '<b>🚨 Incident</b><br>' +
            '<?php echo e($incident?->incident_number ?? "INC-001"); ?>'
        );

    function loadLocations() {
        fetch('<?php echo e(route("admin.gps.locations")); ?>')

            .then(response => response.json())

            .then(data => {

                if (!Array.isArray(data)) return;

                data.forEach(location => {

                    const lat =
                        parseFloat(location.latitude);

                    const lng =
                        parseFloat(location.longitude);

                    if (isNaN(lat) || isNaN(lng))
                        return;

                    const driverId =
                        location.driver_id;

                    if (!markers[driverId]) {

                        markers[driverId] =
                            L.marker([lat, lng])
                            .addTo(map);

                    } else {

                        markers[driverId]
                            .setLatLng([lat, lng]);

                    }

                    if (!routingControl) {

                        routingControl =
                            L.Routing.control({

                                waypoints: [
                                    L.latLng(lat, lng),
                                    L.latLng(
                                        incidentLat,
                                        incidentLng
                                    )
                                ],

                                routeWhileDragging: false,
                                addWaypoints: false,
                                draggableWaypoints: false,
                                fitSelectedRoutes: true,
                                show: false

                            }).addTo(map);

                        routingControl.on(
                            'routesfound',
                            function(e) {

                                const route =
                                    e.routes[0];

                                const distance =
                                    (
                                        route.summary.totalDistance /
                                        1000
                                    ).toFixed(2);

                                const eta =
                                    Math.round(
                                        route.summary.totalTime /
                                        60
                                    );

                                document.getElementById(
                                        'distance'
                                    ).innerHTML =
                                    'Distance: ' +
                                    distance +
                                    ' KM';

                                document.getElementById(
                                        'eta'
                                    ).innerHTML =
                                    eta +
                                    ' Minutes';
                            }
                        );

                    } else {

                        routingControl.setWaypoints([
                            L.latLng(lat, lng),
                            L.latLng(
                                incidentLat,
                                incidentLng
                            )
                        ]);

                    }

                });

            })

            .catch(error => {
                console.error(error);
            });
    }

    loadLocations();

    setInterval(
        loadLocations,
        5000
    );
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/admin/gps-monitoring.blade.php ENDPATH**/ ?>