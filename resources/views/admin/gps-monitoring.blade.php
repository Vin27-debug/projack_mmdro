@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 class="section-heading mb-1">
            GPS Monitoring
        </h2>

        <p class="section-excerpt mb-0">
            Track active rescue vehicles and monitor their missions in real time.
        </p>
    </div>

    <div>
        <span class="badge bg-success px-3 py-2" id="liveStatus">
            ● LIVE
        </span>
    </div>

</div>


{{-- ============================================================
     DRIVER / AMBULANCE CARDS
============================================================ --}}

<div id="ambulanceList" class="row g-3 mb-4">
</div>


{{-- ============================================================
     NO DATA MESSAGE
============================================================ --}}

<div id="noAmbulanceMessage"
    class="alert alert-secondary d-none">

    <strong>No GPS data available.</strong>

    <br>

    There are currently no ambulances with GPS locations.

</div>


{{-- ============================================================
     MAP
============================================================ --}}

<div class="card border-0 shadow-sm rounded-4">

    <div class="card-body p-0">

        <div id="map"
            style="
                height:650px;
                width:100%;
                border-radius:16px;
                overflow:hidden;
             ">
        </div>

    </div>

</div>


{{-- ============================================================
     LEAFLET
============================================================ --}}

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />


<script
    src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js">
</script>

<script
    src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js">
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        'use strict';


        /* ============================================================
           CONFIG
        ============================================================ */

        const locationsUrl =
            @json(route('admin.gps.locations'));

        const UPDATE_INTERVAL = 5000;

        const DEFAULT_LAT = 15.421486;
        const DEFAULT_LNG = 120.842827;
        const DEFAULT_ZOOM = 13;


        /* ============================================================
           MAP
        ============================================================ */

        const map = L.map('map').setView(
            [DEFAULT_LAT, DEFAULT_LNG],
            DEFAULT_ZOOM
        );


        L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,

                attribution: '&copy; OpenStreetMap contributors'
            }
        ).addTo(map);


        /* ============================================================
           STORAGE
        ============================================================ */

        const ambulanceMarkers = {};

        const incidentMarkers = {};

        const routingControls = {};

        let firstLoad = true;

        let updateTimer = null;


        /* ============================================================
           STATUS
        ============================================================ */

        function setLiveStatus(text, type = 'success') {
            const element =
                document.getElementById('liveStatus');

            if (!element) return;

            element.textContent = text;

            element.className =
                'badge px-3 py-2 bg-' + type;
        }


        /* ============================================================
           ESCAPE HTML
        ============================================================ */

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }


        /* ============================================================
           STATUS LABEL
        ============================================================ */

        function getStatusLabel(status) {
            const labels = {

                pending: 'PENDING',

                assigned: 'ASSIGNED',

                accepted: 'ACCEPTED',

                en_route: 'EN ROUTE',

                arrived: 'ARRIVED',

                completed: 'COMPLETED',

                closed: 'CLOSED',

                cancelled: 'CANCELLED',

                no_mission: 'NO ACTIVE MISSION'

            };

            return labels[status] ||
                String(status || 'UNKNOWN')
                .replaceAll('_', ' ')
                .toUpperCase();
        }


        /* ============================================================
           STATUS BADGE
        ============================================================ */

        function getStatusBadge(status) {
            if (status === 'completed') {

                return `
                <span class="badge bg-primary">
                    🔵 COMPLETED
                </span>
            `;

            }

            if (status === 'closed') {

                return `
                <span class="badge bg-secondary">
                    ⚪ CLOSED
                </span>
            `;

            }

            if (status === 'cancelled') {

                return `
                <span class="badge bg-danger">
                    CANCELLED
                </span>
            `;

            }

            if (status === 'arrived') {

                return `
                <span class="badge bg-warning text-dark">
                    🟡 ARRIVED
                </span>
            `;

            }

            if (
                status === 'en_route' ||
                status === 'accepted' ||
                status === 'assigned'
            ) {

                return `
                <span class="badge bg-success">
                    🟢 ${escapeHtml(getStatusLabel(status))}
                </span>
            `;

            }

            return `
            <span class="badge bg-secondary">
                ${escapeHtml(getStatusLabel(status))}
            </span>
        `;
        }


        /* ============================================================
           CREATE AMBULANCE ICON
        ============================================================ */

        function createAmbulanceIcon(status) {
            let background = '#198754';

            if (status === 'completed') {
                background = '#0d6efd';
            }

            if (status === 'arrived') {
                background = '#ffc107';
            }

            if (status === 'cancelled') {
                background = '#dc3545';
            }

            if (status === 'no_mission') {
                background = '#6c757d';
            }

            return L.divIcon({

                className: 'ambulance-marker',

                html: `
                <div style="
                    width:42px;
                    height:42px;
                    border-radius:50%;
                    background:${background};
                    border:3px solid white;
                    box-shadow:0 2px 8px rgba(0,0,0,.35);
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    font-size:21px;
                ">
                    🚑
                </div>
            `,

                iconSize: [42, 42],

                iconAnchor: [21, 21],

                popupAnchor: [0, -22]

            });
        }


        /* ============================================================
           CREATE INCIDENT ICON
        ============================================================ */

        function createIncidentIcon() {
            return L.divIcon({

                className: 'incident-marker',

                html: `
                <div style="
                    width:38px;
                    height:38px;
                    border-radius:50%;
                    background:#dc3545;
                    border:3px solid white;
                    box-shadow:0 2px 8px rgba(0,0,0,.4);
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    font-size:19px;
                ">
                    🚨
                </div>
            `,

                iconSize: [38, 38],

                iconAnchor: [19, 19],

                popupAnchor: [0, -20]

            });
        }


        /* ============================================================
           CREATE / UPDATE AMBULANCE MARKER
        ============================================================ */

        function updateAmbulanceMarker(location) {
            const driverId =
                location.driver_id;

            const lat =
                Number(location.latitude);

            const lng =
                Number(location.longitude);

            if (
                !Number.isFinite(lat) ||
                !Number.isFinite(lng)
            ) {
                return;
            }


            const popup = `

            <div style="min-width:220px">

                <strong>
                    🚑 ${escapeHtml(
                        location.vehicle_name ||
                        'Ambulance'
                    )}
                </strong>

                <hr>

                <div>
                    <strong>Driver:</strong>
                    ${escapeHtml(
                        location.driver_name ||
                        'Unknown'
                    )}
                </div>

                <div>
                    <strong>Status:</strong>
                    ${escapeHtml(
                        getStatusLabel(
                            location.dispatch_status
                        )
                    )}
                </div>

                ${
                    location.incident_number
                    ?
                    `
                    <div>
                        <strong>Incident:</strong>
                        ${escapeHtml(
                            location.incident_number
                        )}
                    </div>
                    `
                    :
                    ''
                }

                <div>
                    <strong>Last GPS:</strong>
                    ${location.recorded_at
                        ? new Date(
                            location.recorded_at
                        ).toLocaleString()
                        : 'Unknown'
                    }
                </div>

            </div>

        `;


            if (!ambulanceMarkers[driverId]) {

                ambulanceMarkers[driverId] =
                    L.marker(
                        [lat, lng], {
                            icon: createAmbulanceIcon(
                                location.monitoring_status === 'completed' ?
                                'completed' :
                                location.dispatch_status
                            )
                        }
                    )
                    .addTo(map);

            } else {

                ambulanceMarkers[driverId]
                    .setLatLng([lat, lng]);

                ambulanceMarkers[driverId]
                    .setIcon(
                        createAmbulanceIcon(
                            location.monitoring_status === 'completed' ?
                            'completed' :
                            location.dispatch_status
                        )
                    );

            }


            ambulanceMarkers[driverId]
                .bindPopup(popup);
        }


        /* ============================================================
           INCIDENT MARKER
        ============================================================ */

        function updateIncidentMarker(location) {
            if (
                !location.incident_id ||
                !location.incident_latitude ||
                !location.incident_longitude
            ) {
                return;
            }


            const incidentId =
                location.incident_id;

            const lat =
                Number(location.incident_latitude);

            const lng =
                Number(location.incident_longitude);


            if (
                !Number.isFinite(lat) ||
                !Number.isFinite(lng)
            ) {
                return;
            }


            const popup = `

            <div>

                <strong>
                    🚨 Incident
                </strong>

                <br>

                ${
                    escapeHtml(
                        location.incident_number ||
                        'Incident'
                    )
                }

                <br>

                ${
                    escapeHtml(
                        location.incident_location ||
                        'Location unavailable'
                    )
                }

            </div>

        `;


            if (!incidentMarkers[incidentId]) {

                incidentMarkers[incidentId] =
                    L.marker(
                        [lat, lng], {
                            icon: createIncidentIcon()
                        }
                    )
                    .addTo(map);

            } else {

                incidentMarkers[incidentId]
                    .setLatLng([lat, lng]);

            }


            incidentMarkers[incidentId]
                .bindPopup(popup);
        }


        /* ============================================================
           ROUTING
        ============================================================ */

        function updateRoute(lat, lng, incidentLat, incidentLng) {

            // Convert incoming values to numbers
            const driverLat = Number(lat);
            const driverLng = Number(lng);

            const destinationLat =
                incidentLat !== null &&
                incidentLat !== undefined &&
                incidentLat !== '' ?
                Number(incidentLat) :
                null;

            const destinationLng =
                incidentLng !== null &&
                incidentLng !== undefined &&
                incidentLng !== '' ?
                Number(incidentLng) :
                null;

            // No destination = no route
            if (
                destinationLat === null ||
                destinationLng === null ||
                !Number.isFinite(driverLat) ||
                !Number.isFinite(driverLng) ||
                !Number.isFinite(destinationLat) ||
                !Number.isFinite(destinationLng)
            ) {
                return;
            }

            // Do not route to 0,0
            if (
                (destinationLat === 0 && destinationLng === 0) ||
                (driverLat === 0 && driverLng === 0)
            ) {
                return;
            }

            console.log('ROUTING:', {
                driver: [driverLat, driverLng],
                incident: [destinationLat, destinationLng]
            });

            // Remove previous route
            if (routingControl) {
                map.removeControl(routingControl);
                routingControl = null;
            }

            // Create route
            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(driverLat, driverLng),
                    L.latLng(destinationLat, destinationLng)
                ],

                router: L.Routing.osrmv1({
                    serviceUrl: 'https://router.project-osrm.org/route/v1'
                }),

                routeWhileDragging: false,
                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: false,
                show: false,

                createMarker: function() {
                    return null;
                }
            }).addTo(map);

            // Route successfully found
            routingControl.on('routesfound', function(e) {

                if (!e.routes || e.routes.length === 0) {
                    return;
                }

                const route = e.routes[0];

                const distanceKm =
                    route.summary.totalDistance / 1000;

                const etaMinutes =
                    Math.max(
                        1,
                        Math.round(route.summary.totalTime / 60)
                    );

                console.log('ROUTE FOUND:', {
                    distance: distanceKm,
                    eta: etaMinutes
                });

                // Update distance
                const distanceElement =
                    document.getElementById('distance');

                // Update ETA
                const etaElement =
                    document.getElementById('eta');

                if (distanceElement) {
                    distanceElement.innerHTML =
                        'Distance: ' +
                        distanceKm.toFixed(2) +
                        ' KM';
                }

                if (etaElement) {
                    etaElement.innerHTML =
                        etaMinutes +
                        ' Minutes';
                }
            });

            // Routing error
            routingControl.on('routingerror', function(e) {
                console.warn('Routing unavailable:', e);
            });
        }

        /* ============================================================
           CARD UPDATE
        ============================================================ */

        function updateCard(location) {
            const card =
                document.getElementById(
                    'ambulance-card-' +
                    location.driver_id
                );

            if (!card) return;


            const eta =
                card.querySelector(
                    '[data-eta]'
                );

            const distance =
                card.querySelector(
                    '[data-distance]'
                );


            if (eta && location._eta) {

                eta.textContent =
                    location._eta +
                    ' min';

            }


            if (distance && location._distance) {

                distance.textContent =
                    location._distance +
                    ' km';

            }
        }


        /* ============================================================
           RENDER CARDS
        ============================================================ */

        function renderCards(locations) {
            const container =
                document.getElementById(
                    'ambulanceList'
                );

            const noMessage =
                document.getElementById(
                    'noAmbulanceMessage'
                );


            if (!locations.length) {

                container.innerHTML = '';

                noMessage.classList.remove(
                    'd-none'
                );

                return;

            }


            noMessage.classList.add(
                'd-none'
            );


            container.innerHTML =
                locations.map(function(location) {

                    const status =
                        location.dispatch_status;


                    const completed =
                        location.monitoring_status ===
                        'completed';


                    return `

                    <div
                        class="col-md-6 col-xl-4"
                        id="ambulance-card-${location.driver_id}"
                    >

                        <div
                            class="card border-0 shadow-sm rounded-4 h-100"
                        >

                            <div class="card-body">

                                <div
                                    class="d-flex justify-content-between align-items-start mb-3"
                                >

                                    <div>

                                        <h5 class="fw-bold mb-1">

                                            🚑
                                            ${escapeHtml(
                                                location.vehicle_name ||
                                                'Ambulance'
                                            )}

                                        </h5>

                                        <small class="text-muted">

                                            Driver:
                                            ${escapeHtml(
                                                location.driver_name ||
                                                'Unknown'
                                            )}

                                        </small>

                                    </div>

                                    <div>

                                        ${
                                            getStatusBadge(
                                                status
                                            )
                                        }

                                    </div>

                                </div>


                                ${
                                    location.incident_number
                                    ?
                                    `
                                    <div class="mb-2">

                                        <strong>
                                            Incident:
                                        </strong>

                                        ${escapeHtml(
                                            location.incident_number
                                        )}

                                    </div>
                                    `
                                    :
                                    `
                                    <div class="mb-2">

                                        <strong>
                                            Mission:
                                        </strong>

                                        No active mission

                                    </div>
                                    `
                                }


                                ${
                                    location.incident_location
                                    ?
                                    `
                                    <div class="mb-2">

                                        <strong>
                                            Location:
                                        </strong>

                                        <br>

                                        <small>
                                            ${escapeHtml(
                                                location.incident_location
                                            )}
                                        </small>

                                    </div>
                                    `
                                    :
                                    ''
                                }


                                <div class="row mt-3">

                                    <div class="col-6">

                                        <small class="text-muted">
                                            Distance
                                        </small>

                                        <div
                                            class="fw-bold"
                                            data-distance
                                        >
                                            ${
                                                completed
                                                ? '—'
                                                : 'Calculating...'
                                            }
                                        </div>

                                    </div>


                                    <div class="col-6">

                                        <small class="text-muted">
                                            ETA
                                        </small>

                                        <div
                                            class="fw-bold"
                                            data-eta
                                        >
                                            ${
                                                completed
                                                ? 'Completed'
                                                : 'Calculating...'
                                            }
                                        </div>

                                    </div>

                                </div>


                                <hr>


                                <small class="text-muted">

                                    Last GPS:

                                    ${
                                        location.recorded_at
                                        ?
                                        new Date(
                                            location.recorded_at
                                        ).toLocaleString()
                                        :
                                        'Unknown'
                                    }

                                </small>


                                ${
                                    completed
                                    ?
                                    `
                                    <div class="mt-2">

                                        <span
                                            class="badge bg-primary"
                                        >
                                            Last Known Location
                                        </span>

                                    </div>
                                    `
                                    :
                                    ''
                                }

                            </div>

                        </div>

                    </div>

                `;

                }).join('');
        }


        /* ============================================================
           LOAD LOCATIONS
        ============================================================ */

        async function loadLocations() {
            try {

                setLiveStatus(
                    '● UPDATING',
                    'warning'
                );


                const response =
                    await fetch(
                        locationsUrl +
                        '?_=' +
                        Date.now(), {
                            headers: {
                                'Accept': 'application/json'
                            },

                            cache: 'no-store'
                        }
                    );


                if (!response.ok) {

                    throw new Error(
                        'GPS request failed: ' +
                        response.status
                    );

                }


                const locations =
                    await response.json();


                if (!Array.isArray(locations)) {

                    throw new Error(
                        'Invalid GPS response'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Render cards
                |--------------------------------------------------------------------------
                */

                renderCards(locations);


                /*
                |--------------------------------------------------------------------------
                | Track currently returned drivers
                |--------------------------------------------------------------------------
                */

                const currentDrivers =
                    new Set(
                        locations.map(
                            location =>
                            String(
                                location.driver_id
                            )
                        )
                    );


                /*
                |--------------------------------------------------------------------------
                | Update every ambulance
                |--------------------------------------------------------------------------
                */

                locations.forEach(function(location) {

                    updateAmbulanceMarker(
                        location
                    );

                    updateIncidentMarker(
                        location
                    );

                    updateRoute(
                        location
                    );

                });


                /*
                |--------------------------------------------------------------------------
                | Remove markers for drivers no longer returned
                |--------------------------------------------------------------------------
                */

                Object.keys(
                    ambulanceMarkers
                ).forEach(function(driverId) {

                    if (
                        !currentDrivers.has(
                            String(driverId)
                        )
                    ) {

                        map.removeLayer(
                            ambulanceMarkers[
                                driverId
                            ]
                        );

                        delete ambulanceMarkers[
                            driverId
                        ];

                    }

                });


                /*
                |--------------------------------------------------------------------------
                | Remove routes for drivers no longer active
                |--------------------------------------------------------------------------
                */

                Object.keys(
                    routingControls
                ).forEach(function(driverId) {

                    const location =
                        locations.find(
                            item =>
                            String(
                                item.driver_id
                            ) ===
                            String(driverId)
                        );


                    if (
                        !location ||
                        location.monitoring_status ===
                        'completed'
                    ) {

                        map.removeControl(
                            routingControls[
                                driverId
                            ]
                        );

                        delete routingControls[
                            driverId
                        ];

                    }

                });


                /*
                |--------------------------------------------------------------------------
                | First load: fit map
                |--------------------------------------------------------------------------
                */

                if (
                    firstLoad &&
                    locations.length
                ) {

                    const points =
                        locations
                        .filter(
                            location =>
                            Number.isFinite(
                                Number(
                                    location.latitude
                                )
                            ) &&
                            Number.isFinite(
                                Number(
                                    location.longitude
                                )
                            )
                        )
                        .map(
                            location => [
                                Number(
                                    location.latitude
                                ),
                                Number(
                                    location.longitude
                                )
                            ]
                        );


                    if (points.length) {

                        map.fitBounds(
                            L.latLngBounds(points), {
                                padding: [
                                    50,
                                    50
                                ],

                                maxZoom: 15
                            }
                        );

                    }

                    firstLoad = false;
                }


                setLiveStatus(
                    '● LIVE',
                    'success'
                );


            } catch (error) {

                console.error(
                    'GPS MONITORING ERROR:',
                    error
                );


                setLiveStatus(
                    '● CONNECTION ERROR',
                    'danger'
                );

            }
        }


        /* ============================================================
           START
        ============================================================ */

        loadLocations();


        updateTimer =
            setInterval(
                loadLocations,
                UPDATE_INTERVAL
            );


        /*
        |--------------------------------------------------------------------------
        | Fix Leaflet sizing
        |--------------------------------------------------------------------------
        */

        setTimeout(
            function() {
                map.invalidateSize();
            },
            500
        );


        window.addEventListener(
            'resize',
            function() {
                map.invalidateSize();
            }
        );

    });
</script>

@endsection