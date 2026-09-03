@extends('layouts.driver')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 mb-1">🗺 Navigation</h2>
            <p class="text-muted mb-0">Track the incident location and your current position.</p>
        </div>
    </div>

    @if(!$dispatch || !$dispatch->incident)
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body text-center py-5">
            <i class="bi bi-exclamation-circle text-warning fs-1 mb-3"></i>
            <h5 class="fw-semibold text-white">No active assignment available</h5>
            <p class="text-muted mb-3">You currently do not have an active dispatch with a mapped incident location.</p>
            <a href="{{ route('driver.dashboard') }}" class="btn btn-primary me-2">Back to Dashboard</a>
            <a href="{{ route('driver.history') }}" class="btn btn-outline-secondary">View Dispatch History</a>
        </div>
    </div>
    @else
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <h5 class="mb-1">{{ $dispatch->incident->incident_number }}</h5>
            <p class="text-muted">{{ $dispatch->incident->location ?? 'Unknown Location' }}</p>
            <p class="small">{{ collect([$dispatch->incident->house_number, $dispatch->incident->street, $dispatch->incident->barangay, $dispatch->incident->city, $dispatch->incident->province])->filter()->implode(', ') }}</p>
            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $dispatch->incident->latitude }},{{ $dispatch->incident->longitude }}" target="_blank" class="btn btn-success">
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
    @endif
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($dispatch && $dispatch->incident)
        const incidentLat = {
            {
                $dispatch->incident->latitude ?? 15.5000
            }
        };
        const incidentLng = {
            {
                $dispatch->incident->longitude ?? 120.8500
            }
        };

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

            navigator.geolocation.getCurrentPosition((position) => {
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

                fetch('{{ route("driver.gps.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        latitude: lat,
                        longitude: lng,
                        speed_kmh: Number.isFinite(position.coords.speed) && position.coords.speed >= 0 ? position.coords.speed * 3.6 : null
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
        @endif
    });
</script>
@endsection