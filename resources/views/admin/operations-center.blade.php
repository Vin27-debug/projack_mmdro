@extends('layouts.admin')

@section('content')

<div class="card shadow-sm border-0 rounded-4">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-4">

        <div>
            <h2 class="section-heading mb-1">
                Live Command Center
            </h2>

            <p class="section-excerpt mb-0">
                Real-time emergency operations overview with live geospatial intelligence.
            </p>
        </div>

        <div class="badge bg-danger text-white px-3 py-2 rounded-pill">
            <i class="bi bi-arrow-repeat"></i>
            Auto-refresh every 10 seconds
        </div>

    </div>


    {{-- =========================================================
         STATISTICS
    ========================================================== --}}
    <div class="row g-3 mb-4">

        {{-- LIVE INCIDENTS --}}
        <div class="col-xl-3 col-md-6">
            <div class="card admin-stat-card border-0 shadow-sm h-100 admin-card">
                <div class="card-body">

                    <div class="small text-uppercase fw-semibold">
                        Live Incidents
                    </div>

                    <div class="display-6 fw-bold mt-2">
                        {{ $stats['live_incidents'] ?? 0 }}
                    </div>

                </div>
            </div>
        </div>


        {{-- ACTIVE AMBULANCES --}}
        <div class="col-xl-3 col-md-6">
            <div class="card admin-stat-card border-0 shadow-sm h-100 admin-card">
                <div class="card-body">

                    <div class="small text-uppercase fw-semibold">
                        Active Ambulances
                    </div>

                    <div class="display-6 fw-bold mt-2">
                        {{ $stats['active_ambulances'] ?? 0 }}
                    </div>

                </div>
            </div>
        </div>


        {{-- PANIC ALERTS --}}
        <div class="col-xl-3 col-md-6">
            <div class="card admin-stat-card border-0 shadow-sm h-100 admin-card">
                <div class="card-body">

                    <div class="small text-uppercase fw-semibold">
                        Panic Alerts
                    </div>

                    <div class="display-6 fw-bold mt-2">
                        {{ $stats['panic_alerts'] ?? 0 }}
                    </div>

                </div>
            </div>
        </div>


        {{-- HIJACK ALERTS --}}
        <div class="col-xl-3 col-md-6">
            <div class="card admin-stat-card border-0 shadow-sm h-100 admin-card">
                <div class="card-body">

                    <div class="small text-uppercase fw-semibold">
                        Hijack Alerts
                    </div>

                    <div class="display-6 fw-bold mt-2">
                        {{ $stats['hijack_alerts'] ?? 0 }}
                    </div>

                </div>
            </div>
        </div>

    </div>


    {{-- =========================================================
         MAIN CONTENT
    ========================================================== --}}
    <div class="row g-4">

        {{-- =====================================================
             MAP
        ====================================================== --}}
        <div class="col-xl-9 col-lg-8">

            <div class="card border-0 shadow-sm overflow-hidden">

                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">

                    <span>
                        <i class="bi bi-map"></i>
                        Live Tactical Map
                    </span>

                    <span class="small opacity-75">
                        Fullscreen-ready Leaflet view
                    </span>

                </div>

                <div class="card-body p-0">

                    <div id="operationsMap"></div>

                </div>

            </div>


            {{-- MAP LEGEND --}}
            <div class="mt-3 d-flex flex-wrap gap-3 align-items-center">

                <strong class="small">
                    Map Legend:
                </strong>

                <span class="small">
                    <span class="legend-dot legend-incident"></span>
                    Incidents
                </span>

                <span class="small">
                    <span class="legend-dot legend-panic"></span>
                    Panic
                </span>

                <span class="small">
                    <span class="legend-dot legend-hijack"></span>
                    Hijack
                </span>

                <span class="small">
                    <span class="legend-dot legend-vehicle"></span>
                    Vehicles
                </span>

            </div>

        </div>


        {{-- =====================================================
             RIGHT SIDEBAR
        ====================================================== --}}
        <div class="col-xl-3 col-lg-4">


            {{-- =================================================
                 ACTIVE DISPATCHES
            ================================================== --}}
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-danger text-white">
                    <i class="bi bi-broadcast"></i>
                    Active Dispatches
                </div>

                <div class="card-body">

                    @forelse($activeDispatches as $dispatch)

                    <div class="border rounded p-3 mb-2">

                        <div class="fw-semibold">
                            {{ $dispatch->incident?->incident_number ?? 'Unassigned incident' }}
                        </div>

                        <div class="small text-muted">
                            Vehicle:
                            {{ $dispatch->vehicle?->plate_number ?? 'N/A' }}
                        </div>

                        <div class="small text-muted">
                            Driver:
                            {{ $dispatch->driver?->user?->name ?? 'Unassigned' }}
                        </div>

                        <div class="mt-2">

                            <span class="badge bg-primary">
                                {{ strtoupper(str_replace('_', ' ', $dispatch->status ?? 'UNKNOWN')) }}
                            </span>

                        </div>

                    </div>

                    @empty

                    <div class="text-muted small">
                        No active dispatches.
                    </div>

                    @endforelse

                </div>

            </div>


            {{-- =================================================
                 ACTIVE VEHICLES
            ================================================== --}}
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-primary text-white">
                    <i class="bi bi-truck"></i>
                    Active Vehicles
                </div>

                <div class="card-body">

                    @forelse(
                    $vehicles->whereIn(
                    'status',
                    ['available', 'on_duty', 'active', 'ready']
                    )
                    as $vehicle
                    )

                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">

                        <div>

                            <div class="fw-semibold">
                                {{ $vehicle->vehicle_name ?? 'Unnamed Vehicle' }}
                            </div>

                            <div class="small text-muted">
                                {{ $vehicle->plate_number ?? 'No plate number' }}
                            </div>

                        </div>

                        <span class="badge bg-success">
                            {{ ucfirst(str_replace('_', ' ', $vehicle->status ?? 'unknown')) }}
                        </span>

                    </div>

                    @empty

                    <div class="text-muted small">
                        No active vehicles.
                    </div>

                    @endforelse

                </div>

            </div>


            {{-- =================================================
                 PENDING INCIDENTS
            ================================================== --}}
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-warning text-dark">

                    <i class="bi bi-exclamation-triangle"></i>
                    Pending Incidents

                </div>

                <div class="card-body">

                    @forelse($incidents->where('status', 'pending') as $incident)

                    <div class="border rounded p-2 mb-2">

                        <div class="fw-semibold">
                            {{ $incident->incident_number ?? 'Incident' }}
                        </div>

                        <div class="small text-muted">
                            {{ $incident->location ?? 'Location unavailable' }}
                        </div>

                        <div class="small mt-1">

                            Status:

                            <span class="badge bg-warning text-dark">
                                Pending
                            </span>

                        </div>

                    </div>

                    @empty

                    <div class="text-muted small">
                        No pending incidents.
                    </div>

                    @endforelse

                </div>

            </div>


            {{-- =================================================
                 PANIC ALERTS
            ================================================== --}}
            <div class="card border-0 shadow-sm mb-4">

                <div class="card-header bg-dark text-white">

                    <i class="bi bi-shield-exclamation"></i>
                    Panic Alerts

                </div>

                <div class="card-body">

                    @forelse($panicAlerts->where('status', 'active') as $alert)

                    <div class="border rounded p-2 mb-2">

                        <div class="fw-semibold">
                            {{ $alert->driver?->user?->name ?? 'Driver' }}
                        </div>

                        <div class="small text-muted">
                            {{ $alert->triggered_at?->diffForHumans() ?? 'Recently triggered' }}
                        </div>

                        <div class="mt-1">

                            <span class="badge bg-danger">
                                ACTIVE
                            </span>

                        </div>

                    </div>

                    @empty

                    <div class="text-muted small">
                        No panic alerts.
                    </div>

                    @endforelse

                </div>

            </div>


            {{-- =================================================
                 HIJACK ALERTS
            ================================================== --}}
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-dark text-white">

                    <i class="bi bi-car-front-fill"></i>
                    Hijack Alerts

                </div>

                <div class="card-body">

                    @forelse($hijackAlerts->where('status', 'active') as $alert)

                    <div class="border rounded p-2 mb-2">

                        <div class="fw-semibold">
                            {{ $alert->driver?->user?->name ?? 'Driver' }}
                        </div>

                        <div class="small text-muted">
                            {{ $alert->triggered_at?->diffForHumans() ?? 'Recently triggered' }}
                        </div>

                        <div class="mt-1">

                            <span class="badge bg-dark">
                                ACTIVE
                            </span>

                        </div>

                    </div>

                    @empty

                    <div class="text-muted small">
                        No hijack alerts.
                    </div>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =============================================================
     LEAFLET CSS
============================================================= --}}

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />


{{-- =============================================================
     LEAFLET JAVASCRIPT
============================================================= --}}

<script
    src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js">
</script>


{{-- =============================================================
     PAGE STYLES
============================================================= --}}

<style>
    #operationsMap {
        width: 100%;
        height: 560px;
        min-height: 560px;
        max-height: 560px;
        border-radius: 0 0 12px 12px;
        overflow: hidden;
        background: #dbeafe;
    }

    .legend-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 4px;
    }

    .legend-incident {
        background: #dc3545;
    }

    .legend-panic {
        background: #fd7e14;
    }

    .legend-hijack {
        background: #000000;
    }

    .legend-vehicle {
        background: #0d6efd;
    }

    .operations-popup-title {
        font-weight: 700;
        margin-bottom: 4px;
    }

    .operations-popup-row {
        font-size: 13px;
        margin-bottom: 2px;
    }

    @media (max-width: 768px) {

        #operationsMap {
            height: 450px;
            min-height: 450px;
            max-height: 450px;
        }

    }
</style>


{{-- =============================================================
     MAP JAVASCRIPT
============================================================= --}}

<script>
    document.addEventListener('DOMContentLoaded', function() {

        /*
        |--------------------------------------------------------------------------
        | CHECK LEAFLET
        |--------------------------------------------------------------------------
        */

        if (typeof L === 'undefined') {

            console.error('Leaflet failed to load.');

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | MAP ELEMENT
        |--------------------------------------------------------------------------
        */

        const mapElement = document.getElementById('operationsMap');

        if (!mapElement) {

            console.error('operationsMap element was not found.');

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | DEFAULT LOCATION
        |--------------------------------------------------------------------------
        | Cabanatuan City
        |--------------------------------------------------------------------------
        */

        const DEFAULT_LATITUDE = 15.4866;
        const DEFAULT_LONGITUDE = 120.9675;
        const DEFAULT_ZOOM = 13;


        /*
        |--------------------------------------------------------------------------
        | CREATE MAP
        |--------------------------------------------------------------------------
        */

        const map = L.map('operationsMap', {

            center: [
                DEFAULT_LATITUDE,
                DEFAULT_LONGITUDE
            ],

            zoom: DEFAULT_ZOOM,

            zoomControl: true,

            attributionControl: true

        });


        /*
        |--------------------------------------------------------------------------
        | OPEN STREET MAP
        |--------------------------------------------------------------------------
        */

        L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,

                attribution: '&copy; OpenStreetMap contributors'
            }
        ).addTo(map);


        /*
        |--------------------------------------------------------------------------
        | MAP DATA FROM LARAVEL
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | This is the correct Blade syntax.
        |
        | DO NOT write:
        |
        | const mapData = {
        |     {
        |         Js::from($mapData)
        |     }
        | };
        |
        |--------------------------------------------------------------------------
        */

        const mapData = @js($mapData);


        /*
        |--------------------------------------------------------------------------
        | DEBUG
        |--------------------------------------------------------------------------
        */

        console.log(
            'Operations Map Data:',
            mapData
        );


        /*
        |--------------------------------------------------------------------------
        | ENSURE ARRAYS EXIST
        |--------------------------------------------------------------------------
        */

        const incidents = Array.isArray(mapData?.incidents) ?
            mapData.incidents :
            [];


        const vehicles = Array.isArray(mapData?.vehicles) ?
            mapData.vehicles :
            [];


        const panicAlerts = Array.isArray(mapData?.panicAlerts) ?
            mapData.panicAlerts :
            [];


        const hijackAlerts = Array.isArray(mapData?.hijackAlerts) ?
            mapData.hijackAlerts :
            [];


        /*
        |--------------------------------------------------------------------------
        | VALID COORDINATES
        |--------------------------------------------------------------------------
        */

        function getCoordinates(item) {

            const latitude = Number(item?.latitude);

            const longitude = Number(item?.longitude);


            if (
                !Number.isFinite(latitude) ||
                latitude < -90 ||
                latitude > 90
            ) {

                return null;

            }


            if (
                !Number.isFinite(longitude) ||
                longitude < -180 ||
                longitude > 180
            ) {

                return null;

            }


            if (
                latitude === 0 &&
                longitude === 0
            ) {

                return null;

            }


            return [
                latitude,
                longitude
            ];

        }


        /*
        |--------------------------------------------------------------------------
        | MAP STYLES
        |--------------------------------------------------------------------------
        */

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
                radius: 8,
                fillColor: '#0d6efd'
            }

        };


        /*
        |--------------------------------------------------------------------------
        | ESCAPE HTML
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {

            if (
                value === null ||
                value === undefined
            ) {

                return '';

            }


            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

        }


        /*
        |--------------------------------------------------------------------------
        | ADD MARKER
        |--------------------------------------------------------------------------
        */

        function addMarker(item, type) {

            const coordinates = getCoordinates(item);


            if (!coordinates) {

                console.warn(
                    'Invalid map coordinates:',
                    item
                );

                return null;

            }


            const style =
                layerStyles[type] ||
                layerStyles.vehicle;


            /*
            |--------------------------------------------------------------------------
            | VEHICLE
            |--------------------------------------------------------------------------
            */

            if (type === 'vehicle') {

                const marker = L.circleMarker(
                    coordinates, {
                        radius: style.radius,

                        color: style.color,

                        fillColor: style.fillColor,

                        fillOpacity: 0.95,

                        weight: 3
                    }
                ).addTo(map);


                marker.bindPopup(`

                <div class="operations-popup-title">
                    ${escapeHtml(item.title || 'Vehicle')}
                </div>

                <div class="operations-popup-row">
                    <strong>Plate:</strong>
                    ${escapeHtml(item.plate_number || 'N/A')}
                </div>

                <div class="operations-popup-row">
                    <strong>Status:</strong>
                    ${escapeHtml(item.status || 'Unknown')}
                </div>

            `);


                return marker;

            }


            /*
            |--------------------------------------------------------------------------
            | INCIDENT / ALERT
            |--------------------------------------------------------------------------
            */

            const marker = L.circleMarker(
                coordinates, {
                    radius: style.radius,

                    color: style.color,

                    fillColor: style.fillColor,

                    fillOpacity: 0.85,

                    weight: 2
                }
            ).addTo(map);


            /*
            |--------------------------------------------------------------------------
            | OPTIONAL DRIVER
            |--------------------------------------------------------------------------
            */

            const driverHtml = item.driver_name ?
                `
                <div class="operations-popup-row">
                    <strong>Driver:</strong>
                    ${escapeHtml(item.driver_name)}
                </div>
            ` :
                '';


            /*
            |--------------------------------------------------------------------------
            | OPTIONAL TRIGGERED TIME
            |--------------------------------------------------------------------------
            */

            const triggeredHtml = item.triggered_at ?
                `
                <div class="operations-popup-row">
                    <strong>Triggered:</strong>
                    ${escapeHtml(item.triggered_at)}
                </div>
            ` :
                '';


            /*
            |--------------------------------------------------------------------------
            | POPUP
            |--------------------------------------------------------------------------
            */

            marker.bindPopup(`

            <div class="operations-popup-title">
                ${escapeHtml(item.title || 'Alert')}
            </div>

            <div class="operations-popup-row">
                <strong>Type:</strong>
                ${escapeHtml(type)}
            </div>

            <div class="operations-popup-row">
                <strong>Location:</strong>
                ${escapeHtml(item.location || 'N/A')}
            </div>

            <div class="operations-popup-row">
                <strong>Status:</strong>
                ${escapeHtml(item.status || 'Unknown')}
            </div>

            ${driverHtml}

            ${triggeredHtml}

        `);


            return marker;

        }


        /*
        |--------------------------------------------------------------------------
        | VALID MAP POINTS
        |--------------------------------------------------------------------------
        */

        const validPoints = [];


        /*
        |--------------------------------------------------------------------------
        | INCIDENTS
        |--------------------------------------------------------------------------
        */

        incidents.forEach(function(item) {

            const coordinates =
                getCoordinates(item);


            if (coordinates) {

                validPoints.push(coordinates);

                addMarker(
                    item,
                    'incident'
                );

            }

        });


        /*
        |--------------------------------------------------------------------------
        | PANIC ALERTS
        |--------------------------------------------------------------------------
        */

        panicAlerts.forEach(function(item) {

            const coordinates =
                getCoordinates(item);


            if (coordinates) {

                validPoints.push(coordinates);

                addMarker(
                    item,
                    'panic'
                );

            }

        });


        /*
        |--------------------------------------------------------------------------
        | HIJACK ALERTS
        |--------------------------------------------------------------------------
        */

        hijackAlerts.forEach(function(item) {

            const coordinates =
                getCoordinates(item);


            if (coordinates) {

                validPoints.push(coordinates);

                addMarker(
                    item,
                    'hijack'
                );

            }

        });


        /*
        |--------------------------------------------------------------------------
        | VEHICLES
        |--------------------------------------------------------------------------
        */

        vehicles.forEach(function(item) {

            const coordinates =
                getCoordinates(item);


            if (coordinates) {

                validPoints.push(coordinates);

                addMarker(
                    item,
                    'vehicle'
                );

            }

        });


        /*
        |--------------------------------------------------------------------------
        | FIT MAP
        |--------------------------------------------------------------------------
        */

        if (validPoints.length === 1) {

            map.setView(
                validPoints[0],
                15
            );

        } else if (validPoints.length > 1) {

            const bounds =
                L.latLngBounds(validPoints);


            map.fitBounds(
                bounds, {
                    padding: [
                        40,
                        40
                    ],

                    maxZoom: 15
                }
            );

        } else {

            map.setView(
                [
                    DEFAULT_LATITUDE,
                    DEFAULT_LONGITUDE
                ],
                DEFAULT_ZOOM
            );

        }


        /*
        |--------------------------------------------------------------------------
        | FIX MAP SIZE
        |--------------------------------------------------------------------------
        */

        setTimeout(function() {

            map.invalidateSize();

        }, 300);


        /*
        |--------------------------------------------------------------------------
        | AUTO REFRESH
        |--------------------------------------------------------------------------
        */

        setTimeout(function() {

            window.location.reload();

        }, 10000);

    });
</script>

@endsection