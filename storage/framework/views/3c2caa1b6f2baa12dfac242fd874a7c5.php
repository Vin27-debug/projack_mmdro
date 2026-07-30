

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 mb-1">🗺 Navigation</h2>
            <p class="text-muted mb-0">Track the incident location and your current position.</p>
        </div>
    </div>

    <?php if(!$dispatch || !$dispatch->incident): ?>
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body text-center py-5">
            <i class="bi bi-exclamation-circle text-warning fs-1 mb-3"></i>
            <h5 class="fw-semibold">No active assignment available</h5>
            <p class="text-muted mb-3">You currently do not have an active dispatch with a mapped incident location.</p>
            <a href="<?php echo e(route('driver.dashboard')); ?>" class="btn btn-primary me-2">Back to Dashboard</a>
            <a href="<?php echo e(route('driver.history')); ?>" class="btn btn-outline-secondary">View Dispatch History</a>
        </div>
    </div>
    <?php else: ?>
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <h5 class="mb-1"><?php echo e($dispatch->incident->incident_number); ?></h5>
            <p class="text-muted"><?php echo e($dispatch->incident->location ?? 'Unknown Location'); ?></p>
            <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo e($dispatch->incident->latitude); ?>,<?php echo e($dispatch->incident->longitude); ?>" target="_blank" class="btn btn-success">
                🧭 Open in Google Maps
            </a>
        </div>
    </div>

    <div class="row mb-3">

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small>Distance</small>
                <h5 id="distance">Calculating...</h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <small>ETA</small>
                <h5 id="eta">Calculating...</h5>
            </div>
        </div>
    </div>

</div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div id="map" class="rounded-3" style="height: 500px; width: 100%;"></div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if($dispatch && $dispatch->incident): ?>
        const incidentLat = <?php echo e($dispatch->incident->latitude ?? 15.5000); ?>;
const incidentLng = <?php echo e($dispatch->incident->longitude ?? 120.8500); ?>;

        const map = L.map('map', {
            zoomControl: true,
            scrollWheelZoom: true
        }).setView([incidentLat, incidentLng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const incidentMarker = L.marker([incidentLat, incidentLng]).addTo(map)
            .bindPopup('🚨 Incident Location');

        let driverMarker = null;
        let routeLine = null;

        function updateDriverLocation() {
            if (!navigator.geolocation) {
                return;
            }

            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                if (driverMarker === null) {
                    driverMarker = L.marker([lat, lng]).addTo(map);
                } else {
                    driverMarker.setLatLng([lat, lng]);
                }

                driverMarker.bindPopup('🚑 Your Current Location');

                if (routeLine) {
                    map.removeLayer(routeLine);
                }

                routeLine = L.polyline([
                    [lat, lng],
                    [incidentLat, incidentLng]
                ], {
                    color: '#0d6efd',
                    weight: 4,
                    opacity: 0.8
                }).addTo(map);

                map.fitBounds([
                    [lat, lng],
                    [incidentLat, incidentLng]
                ], {
                    padding: [40, 40]
                });

                fetch('<?php echo e(route("driver.gps.update")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng
                    })
                });
            }, function() {
                incidentMarker.openPopup();
            });
        }

        updateDriverLocation();
        setInterval(updateDriverLocation, 5000);
        setTimeout(function() {
            map.invalidateSize();
        }, 250);
        <?php endif; ?>
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.driver', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\muniresq-project\resources\views/driver/navigation.blade.php ENDPATH**/ ?>