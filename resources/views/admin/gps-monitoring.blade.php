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
     AMBULANCE CARDS
============================================================ --}}

<div id="ambulanceList" class="row g-3 mb-4"></div>


{{-- ============================================================
     NO GPS DATA
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
    href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css">


<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>

<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>


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
                    🟢 ${escapeHtml(
                        getStatusLabel(status)
                    )}
                </span>
            `;
            }


            return `
            <span class="badge bg-secondary">
                ${escapeHtml(
                    getStatusLabel(status)
                )}
            </span>
        `;
        }


        /* ============================================================
           AMBULANCE ICON
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
           INCIDENT ICON
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
           UPDATE AMBULANCE MARKER
        ============================================================ */

        function updateAmbulanceMarker(location) {

            const driverId =
                String(location.driver_id);


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


            const status =
                location.monitoring_status ||
                location.dispatch_status ||
                'no_mission';


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
                        'Unknown Driver'
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
                    ${
                        location.recorded_at
                        ?
                        new Date(
                            location.recorded_at
                        ).toLocaleString()
                        :
                        'Unknown'
                    }
                </div>

            </div>
        `;


            /*
            |--------------------------------------------------------------------------
            | CREATE MARKER
            |--------------------------------------------------------------------------
            */

            if (!ambulanceMarkers[driverId]) {

                ambulanceMarkers[driverId] =
                    L.marker(
                        [lat, lng], {
                            icon: createAmbulanceIcon(
                                status
                            )
                        }
                    ).addTo(map);

            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE MARKER
            |--------------------------------------------------------------------------
            */
            else {

                ambulanceMarkers[driverId]
                    .setLatLng([lat, lng]);

                ambulanceMarkers[driverId]
                    .setIcon(
                        createAmbulanceIcon(
                            status
                        )
                    );
            }


            ambulanceMarkers[driverId]
                .bindPopup(popup);
        }


        /* ============================================================
           UPDATE INCIDENT MARKER
        ============================================================ */

        function updateIncidentMarker(location) {

            if (
                location.incident_id === null ||
                location.incident_id === undefined
            ) {

                return;
            }


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


            if (lat === 0 && lng === 0) {

                return;
            }


            const incidentId =
                String(location.incident_id);


            const popup = `

            <div style="min-width:200px">

                <strong>
                    🚨 Incident
                </strong>

                <hr>

                <div>
                    <strong>Incident:</strong>
                    ${escapeHtml(
                        location.incident_number ||
                        'Incident'
                    )}
                </div>

                <div>
                    <strong>Location:</strong>
                    ${escapeHtml(
                        location.incident_location ||
                        'Location unavailable'
                    )}
                </div>

            </div>

        `;


            /*
            |--------------------------------------------------------------------------
            | CREATE
            |--------------------------------------------------------------------------
            */

            if (!incidentMarkers[incidentId]) {

                incidentMarkers[incidentId] =
                    L.marker(
                        [lat, lng], {
                            icon: createIncidentIcon()
                        }
                    ).addTo(map);

            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE
            |--------------------------------------------------------------------------
            */
            else {

                incidentMarkers[incidentId]
                    .setLatLng([lat, lng]);
            }


            incidentMarkers[incidentId]
                .bindPopup(popup);
        }


        /* ============================================================
           REMOVE ROUTE SAFELY
        ============================================================ */

        function removeRoute(driverId) {

            driverId =
                String(driverId);


            const route =
                routingControls[driverId];


            if (!route) {

                return;
            }


            try {

                if (map && map.hasLayer(route)) {

                    map.removeControl(route);

                }

            } catch (error) {

                console.warn(
                    'Route removal skipped:',
                    error
                );

            }


            delete routingControls[driverId];
        }


        /* ============================================================
           UPDATE ROUTE
        ============================================================ */

        function updateRoute(location) {

            const driverId =
                String(location.driver_id);


            /*
            |--------------------------------------------------------------------------
            | Only active missions need routing
            |--------------------------------------------------------------------------
            */

            const activeStatuses = [

                'pending',

                'assigned',

                'accepted',

                'en_route',

                'arrived'

            ];


            const status =
                String(
                    location.dispatch_status || ''
                );


            /*
            |--------------------------------------------------------------------------
            | No active mission
            |--------------------------------------------------------------------------
            */

            if (
                !location.has_active_mission ||
                !activeStatuses.includes(status)
            ) {

                removeRoute(driverId);

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | DRIVER GPS
            |--------------------------------------------------------------------------
            */

            const driverLat =
                Number(location.latitude);


            const driverLng =
                Number(location.longitude);


            /*
            |--------------------------------------------------------------------------
            | INCIDENT GPS
            |--------------------------------------------------------------------------
            */

            const incidentLat =
                Number(location.incident_latitude);


            const incidentLng =
                Number(location.incident_longitude);


            /*
            |--------------------------------------------------------------------------
            | Validate coordinates
            |--------------------------------------------------------------------------
            */

            if (
                !Number.isFinite(driverLat) ||
                !Number.isFinite(driverLng) ||
                !Number.isFinite(incidentLat) ||
                !Number.isFinite(incidentLng)
            ) {

                removeRoute(driverId);

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Ignore 0,0
            |--------------------------------------------------------------------------
            */

            if (
                (driverLat === 0 && driverLng === 0) ||
                (incidentLat === 0 && incidentLng === 0)
            ) {

                removeRoute(driverId);

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | If route already exists:
            | update waypoints instead of creating another route.
            |--------------------------------------------------------------------------
            */

            if (routingControls[driverId]) {

                try {

                    const existingRoute =
                        routingControls[driverId];

                    existingRoute.setWaypoints([

                        L.latLng(
                            driverLat,
                            driverLng
                        ),

                        L.latLng(
                            incidentLat,
                            incidentLng
                        )

                    ]);

                    return;

                } catch (error) {

                    console.warn(
                        'Existing route invalid. Recreating...',
                        error
                    );

                    removeRoute(driverId);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | CREATE ROUTE
            |--------------------------------------------------------------------------
            */

            const control =
                L.Routing.control({

                    waypoints: [

                        L.latLng(
                            driverLat,
                            driverLng
                        ),

                        L.latLng(
                            incidentLat,
                            incidentLng
                        )

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

                });


            routingControls[driverId] =
                control;


            control.addTo(map);


            /*
            |--------------------------------------------------------------------------
            | ROUTE FOUND
            |--------------------------------------------------------------------------
            */

            control.on(
                'routesfound',
                function(event) {

                    if (
                        !event.routes ||
                        !event.routes.length
                    ) {

                        return;
                    }


                    const route =
                        event.routes[0];


                    if (
                        !route ||
                        !route.summary
                    ) {

                        return;
                    }


                    const distanceKm =
                        route.summary.totalDistance /
                        1000;


                    const etaMinutes =
                        Math.max(
                            1,
                            Math.round(
                                route.summary.totalTime /
                                60
                            )
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Find corresponding card
                    |--------------------------------------------------------------------------
                    */

                    const card =
                        document.getElementById(
                            'ambulance-card-' +
                            driverId
                        );


                    if (!card) {

                        return;
                    }


                    const distanceElement =
                        card.querySelector(
                            '[data-distance]'
                        );


                    const etaElement =
                        card.querySelector(
                            '[data-eta]'
                        );


                    if (distanceElement) {

                        distanceElement.textContent =
                            distanceKm.toFixed(2) +
                            ' km';
                    }


                    if (etaElement) {

                        etaElement.textContent =
                            etaMinutes +
                            ' min';
                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | ROUTING ERROR
            |--------------------------------------------------------------------------
            */

            control.on(
                'routingerror',
                function(error) {

                    console.warn(
                        'Routing unavailable:',
                        error
                    );

                }
            );
        }


        /* ============================================================
           REMOVE OLD MARKERS
        ============================================================ */

        function removeOldMarkers(locations) {

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
            | AMBULANCE MARKERS
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

                    const marker =
                        ambulanceMarkers[
                            driverId
                        ];


                    if (
                        marker &&
                        map.hasLayer(marker)
                    ) {

                        map.removeLayer(marker);
                    }


                    delete ambulanceMarkers[
                        driverId
                    ];
                }

            });


            /*
            |--------------------------------------------------------------------------
            | ROUTES
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


                if (!location) {

                    removeRoute(driverId);

                    return;
                }


                const activeStatuses = [

                    'pending',

                    'assigned',

                    'accepted',

                    'en_route',

                    'arrived'

                ];


                if (
                    !location.has_active_mission ||
                    !activeStatuses.includes(
                        String(
                            location.dispatch_status ||
                            ''
                        )
                    )
                ) {

                    removeRoute(driverId);
                }

            });
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
                        location.dispatch_status ||
                        'no_mission';


                    const completed =
                        location.monitoring_status ===
                        'completed';


                    return `

                <div
                    class="col-md-6 col-xl-4"
                    id="ambulance-card-${escapeHtml(
                        location.driver_id
                    )}"
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
                                            'Unknown Driver'
                                        )}

                                    </small>

                                </div>


                                <div>

                                    ${getStatusBadge(status)}

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
                                            ?
                                            '—'
                                            :
                                            location.has_active_mission
                                            ?
                                            'Calculating...'
                                            :
                                            '—'
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
                                            ?
                                            'Completed'
                                            :
                                            location.has_active_mission
                                            ?
                                            'Calculating...'
                                            :
                                            '—'
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

                            method: 'GET',

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
                | RENDER CARDS
                |--------------------------------------------------------------------------
                */

                renderCards(locations);


                /*
                |--------------------------------------------------------------------------
                | UPDATE MARKERS
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
                | REMOVE OLD MARKERS / ROUTES
                |--------------------------------------------------------------------------
                */

                removeOldMarkers(
                    locations
                );


                /*
                |--------------------------------------------------------------------------
                | FIRST LOAD MAP FIT
                |--------------------------------------------------------------------------
                */

                if (
                    firstLoad &&
                    locations.length
                ) {

                    const points =
                        locations
                        .filter(function(location) {

                            return (
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
                            );

                        })
                        .map(function(location) {

                            return [

                                Number(
                                    location.latitude
                                ),

                                Number(
                                    location.longitude
                                )

                            ];

                        });


                    if (points.length) {

                        map.fitBounds(

                            L.latLngBounds(
                                points
                            ),

                            {

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
           START MONITORING
        ============================================================ */

        loadLocations();


        updateTimer =
            setInterval(
                loadLocations,
                UPDATE_INTERVAL
            );


        /* ============================================================
           MAP SIZE FIX
        ============================================================ */

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


        /* ============================================================
           CLEANUP
        ============================================================ */

        window.addEventListener(
            'beforeunload',
            function() {

                if (updateTimer) {

                    clearInterval(
                        updateTimer
                    );
                }


                Object.keys(
                    routingControls
                ).forEach(function(driverId) {

                    removeRoute(
                        driverId
                    );

                });

            }
        );

    });
</script>

@endsection