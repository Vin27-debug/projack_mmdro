@extends('layouts.admin')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">

<style>
    /* Fix dropdown text visibility in dark admin theme */
    .form-select {
        color: #ffffff !important;
        background-color: #0f1b33 !important;
        border-color: #334155 !important;
    }

    .form-select option {
        color: #000000 !important;
        background-color: #ffffff !important;
    }

    .form-select option:checked {
        color: #ffffff !important;
        background-color: #2563eb !important;
    }

    .form-select:disabled {
        color: #94a3b8 !important;
        background-color: #e5e7eb !important;
    }

    /* Helps Chrome/Windows render native dropdown correctly */
    select.form-select {
        color-scheme: dark;
    }

    select.form-select option {
        color-scheme: light;
    }

    /* Fix text visibility while typing */
    .form-control,
    .form-select,
    textarea {
        color: #ffffff !important;
        background-color: #0f1b33 !important;
        border-color: #334155 !important;
    }

    .form-control::placeholder,
    textarea::placeholder {
        color: #94a3b8 !important;
        opacity: 1 !important;
    }

    .form-control:focus,
    .form-select:focus,
    textarea:focus {
        color: #ffffff !important;
        background-color: #0f1b33 !important;
    }
</style>

<div class="container">
    <h1 class="mb-4">Create Incident</h1>

    <form method="POST" action="{{ route('admin.incidents.store') }}">
        @csrf

        <div class="mb-3">
            <label for="reporter_name" class="form-label">Reporter Name</label>
            <input type="text" id="reporter_name" name="reporter_name"
                class="form-control" value="{{ old('reporter_name') }}" required>
        </div>

        <div class="mb-3">
            <label for="contact_number" class="form-label">Contact Number</label>
            <input type="text" id="contact_number" name="contact_number"
                class="form-control" value="{{ old('contact_number') }}"
                placeholder="09XXXXXXXXX">
        </div>

        <div class="mb-3">
            <label for="incident_type" class="form-label">Incident Type</label>
            <input type="text" id="incident_type" name="incident_type"
                class="form-control" value="{{ old('incident_type') }}"
                placeholder="Example: Medical Emergency, Fire, Accident" required>
        </div>

        <div class="mb-4">
            <label for="priority" class="form-label">Priority</label>
            <select id="priority" name="priority" class="form-select">
                <option value="Low" {{ old('priority') === 'Low' ? 'selected' : '' }}>🟢 Low</option>
                <option value="Medium" {{ old('priority', 'Medium') === 'Medium' ? 'selected' : '' }}>🟡 Medium</option>
                <option value="High" {{ old('priority') === 'High' ? 'selected' : '' }}>🟠 High</option>
                <option value="Critical" {{ old('priority') === 'Critical' ? 'selected' : '' }}>🔴 Critical</option>
            </select>
        </div>

        <div class="mb-4">
            <h4 class="mb-3">Incident Location</h4>

            <div class="mb-3">
                <label for="province" class="form-label">Province</label>
                <select id="province" name="province" class="form-select" required>
                    <option value="">Select Province</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="city" class="form-label">City / Municipality</label>
                <select id="city" name="city" class="form-select" required disabled>
                    <option value="">Select City / Municipality</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="barangay" class="form-label">Barangay</label>
                <select id="barangay" name="barangay" class="form-select" required disabled>
                    <option value="">Select Barangay</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="street" class="form-label">Street / Purok / Landmark</label>
                <input type="text" id="street" name="street" class="form-control"
                    value="{{ old('street') }}"
                    placeholder="Example: Purok 3, Bonifacio Barangay Hall"
                    autocomplete="off">
                <small class="text-muted">
                    Enter a street, purok, landmark, school, subdivision, or nearby place.
                </small>
            </div>

            <div class="mb-3">
                <label for="full_location" class="form-label">Full Location</label>
                <input type="text" id="full_location" name="location"
                    class="form-control" value="{{ old('location') }}" readonly>
                <small id="locationStatus" class="text-muted">
                    Select Province → City/Municipality → Barangay, then enter the street or landmark.
                </small>
            </div>

            <div class="mb-3">
                <label class="form-label">Incident Map</label>

                <div id="map"
                    style="height:450px;width:100%;border-radius:10px;overflow:hidden;"></div>

                <div class="d-flex flex-wrap gap-2 mt-2">
                    <button type="button" id="searchLocationBtn" class="btn btn-primary btn-sm">
                        📍 Search Location
                    </button>
                    <button type="button" id="useMapPointBtn" class="btn btn-outline-secondary btn-sm">
                        📌 Use Current Map Point
                    </button>
                    <button type="button" id="resetMapBtn" class="btn btn-outline-secondary btn-sm">
                        ↩ Reset Map
                    </button>
                </div>

                <small class="text-muted d-block mt-2">
                    If a purok or landmark cannot be found automatically, click the map
                    or drag the marker to the exact incident location.
                </small>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="latitude_display" class="form-label">Latitude</label>
                    <input type="text" id="latitude_display" class="form-control" readonly>
                    <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="longitude_display" class="form-label">Longitude</label>
                    <input type="text" id="longitude_display" class="form-control" readonly>
                    <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" rows="4" class="form-control"
                placeholder="Describe the incident...">{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Save Incident</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        const province = document.getElementById('province');
        const city = document.getElementById('city');
        const barangay = document.getElementById('barangay');
        const street = document.getElementById('street');
        const fullLocation = document.getElementById('full_location');

        const latitude = document.getElementById('latitude');
        const longitude = document.getElementById('longitude');
        const latitudeDisplay = document.getElementById('latitude_display');
        const longitudeDisplay = document.getElementById('longitude_display');

        const locationStatus = document.getElementById('locationStatus');
        const searchLocationBtn = document.getElementById('searchLocationBtn');
        const useMapPointBtn = document.getElementById('useMapPointBtn');
        const resetMapBtn = document.getElementById('resetMapBtn');

        if (!province || !city || !barangay || !street || !fullLocation) {
            console.error('Incident location form elements are missing.');
            return;
        }

        if (typeof L === 'undefined') {
            console.error('Leaflet was not loaded.');
            return;
        }

        const PSGC_BASE = 'https://psgc.cloud/api/v2';
        const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/search';
        const PHOTON_URL = 'https://photon.komoot.io/api/';

        const DEFAULT_LAT = 15.4859;
        const DEFAULT_LNG = 120.9660;
        const DEFAULT_ZOOM = 13;

        let map = null;
        let marker = null;
        let searchTimer = null;
        let searchRequestId = 0;

        function getSelectedText(element) {
            if (!element?.selectedOptions?.length) return '';

            const value = element.selectedOptions[0].textContent.trim();

            if (
                !value ||
                value === 'Select Province' ||
                value === 'Select City / Municipality' ||
                value === 'Select Barangay' ||
                value.startsWith('Loading ')
            ) {
                return '';
            }

            return value;
        }

        function setStatus(message, type = 'muted') {
            if (!locationStatus) return;

            locationStatus.textContent = message;

            locationStatus.classList.remove(
                'text-muted',
                'text-success',
                'text-warning',
                'text-danger'
            );

            locationStatus.classList.add('text-' + type);
        }

        function setCoordinates(lat, lng) {
            lat = Number(lat);
            lng = Number(lng);

            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

            latitude.value = lat.toFixed(7);
            longitude.value = lng.toFixed(7);
            latitudeDisplay.value = lat.toFixed(6);
            longitudeDisplay.value = lng.toFixed(6);
        }

        function clearSelect(element, placeholder) {
            element.innerHTML = '';
            element.appendChild(new Option(placeholder, ''));
        }

        function setLoading(element, text) {
            clearSelect(element, text);
            element.disabled = true;
        }

        async function fetchJson(url, options = {}) {
            const response = await fetch(url, options);

            if (!response.ok) {
                throw new Error('Request failed: ' + response.status);
            }

            return await response.json();
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function updateFullLocation() {
            const parts = [
                street.value.trim(),
                getSelectedText(barangay),
                getSelectedText(city),
                getSelectedText(province)
            ].filter(Boolean);

            fullLocation.value = parts.join(', ');

            console.log('FULL LOCATION:', fullLocation.value);
        }

        // ============================================================
        // MAP
        // ============================================================

        function initializeMap() {
            map = L.map('map').setView(
                [DEFAULT_LAT, DEFAULT_LNG],
                DEFAULT_ZOOM
            );

            L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }
            ).addTo(map);

            marker = L.marker(
                [DEFAULT_LAT, DEFAULT_LNG], {
                    draggable: true,
                    autoPan: true
                }
            ).addTo(map);

            setCoordinates(DEFAULT_LAT, DEFAULT_LNG);

            marker.bindPopup(
                '<strong>Incident Location</strong><br>Cabanatuan City'
            );

            marker.on('dragend', function(event) {
                const position = event.target.getLatLng();

                setCoordinates(position.lat, position.lng);

                setStatus(
                    'Marker moved. Coordinates updated.',
                    'success'
                );
            });

            map.on('click', function(event) {
                const lat = event.latlng.lat;
                const lng = event.latlng.lng;

                marker.setLatLng([lat, lng]);
                setCoordinates(lat, lng);

                marker.bindPopup(
                    '<strong>Selected Incident Location</strong>'
                ).openPopup();

                setStatus(
                    'Map point selected. Coordinates updated.',
                    'success'
                );
            });

            setTimeout(() => map.invalidateSize(), 300);
        }

        // ============================================================
        // PSGC
        // ============================================================

        async function loadProvinces() {
            try {
                setLoading(province, 'Loading provinces...');

                const response = await fetchJson(
                    PSGC_BASE + '/provinces'
                );

                const provinces = Array.isArray(response) ?
                    response :
                    (response.data || []);

                clearSelect(province, 'Select Province');

                provinces.forEach(item => {
                    province.appendChild(
                        new Option(item.name, item.code)
                    );
                });

                province.disabled = false;

                const oldProvince = @json(old('province'));

                if (oldProvince) {
                    province.value = oldProvince;
                    province.dispatchEvent(new Event('change'));
                }

                console.log('PROVINCES LOADED:', provinces.length);

            } catch (error) {
                console.error('PROVINCE ERROR:', error);

                clearSelect(
                    province,
                    'Failed to load provinces'
                );

                setStatus(
                    'Unable to load provinces. Check your internet connection.',
                    'danger'
                );
            }
        }

        async function loadCities(provinceCode) {
            setLoading(
                city,
                'Loading cities / municipalities...'
            );

            clearSelect(
                barangay,
                'Select Barangay'
            );

            barangay.disabled = true;

            if (!provinceCode) {
                clearSelect(
                    city,
                    'Select City / Municipality'
                );
                return;
            }

            try {
                const response = await fetchJson(
                    PSGC_BASE +
                    '/provinces/' +
                    encodeURIComponent(provinceCode) +
                    '/cities-municipalities'
                );

                const cities = Array.isArray(response) ?
                    response :
                    (response.data || []);

                clearSelect(
                    city,
                    'Select City / Municipality'
                );

                cities.forEach(item => {
                    city.appendChild(
                        new Option(item.name, item.code)
                    );
                });

                city.disabled = false;

                const oldCity = @json(old('city'));

                if (oldCity) {
                    city.value = oldCity;
                    city.dispatchEvent(new Event('change'));
                }

                console.log('CITIES LOADED:', cities.length);

            } catch (error) {
                console.error('CITY ERROR:', error);

                clearSelect(
                    city,
                    'Failed to load cities'
                );

                setStatus(
                    'Unable to load cities / municipalities.',
                    'danger'
                );
            }
        }

        async function loadBarangays(cityCode) {
            setLoading(
                barangay,
                'Loading barangays...'
            );

            if (!cityCode) {
                clearSelect(
                    barangay,
                    'Select Barangay'
                );
                return;
            }

            try {
                const response = await fetchJson(
                    PSGC_BASE +
                    '/cities-municipalities/' +
                    encodeURIComponent(cityCode) +
                    '/barangays'
                );

                const barangays = Array.isArray(response) ?
                    response :
                    (response.data || []);

                clearSelect(
                    barangay,
                    'Select Barangay'
                );

                barangays.forEach(item => {
                    barangay.appendChild(
                        new Option(item.name, item.code)
                    );
                });

                barangay.disabled = false;

                const oldBarangay = @json(old('barangay'));

                if (oldBarangay) {
                    barangay.value = oldBarangay;
                    barangay.dispatchEvent(new Event('change'));
                }

                console.log('BARANGAYS LOADED:', barangays.length);

            } catch (error) {
                console.error('BARANGAY ERROR:', error);

                clearSelect(
                    barangay,
                    'Failed to load barangays'
                );

                setStatus(
                    'Unable to load barangays.',
                    'danger'
                );
            }
        }

        // ============================================================
        // SMART SEARCH
        // ============================================================

        function buildSearchQueries() {
            const provinceText = getSelectedText(province);
            const cityText = getSelectedText(city);
            const barangayText = getSelectedText(barangay);
            const streetText = street.value.trim();

            const queries = [];

            if (streetText) {
                // Most specific: school / landmark + barangay + city + province
                queries.push([
                    streetText,
                    barangayText,
                    cityText,
                    provinceText,
                    'Philippines'
                ].filter(Boolean).join(', '));

                // Shorter query helps when the long address is not indexed.
                queries.push([
                    streetText,
                    cityText,
                    provinceText,
                    'Philippines'
                ].filter(Boolean).join(', '));

                queries.push([
                    streetText,
                    cityText,
                    'Philippines'
                ].filter(Boolean).join(', '));
            }

            if (barangayText) {
                queries.push([
                    barangayText,
                    cityText,
                    provinceText,
                    'Philippines'
                ].filter(Boolean).join(', '));
            }

            if (cityText) {
                queries.push([
                    cityText,
                    provinceText,
                    'Philippines'
                ].filter(Boolean).join(', '));
            }

            return [...new Set(queries)];
        }

        function normalizeText(value) {
            return String(value || '')
                .toLowerCase()
                .replace(/[.,()'"’\-_/]/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function scoreResult(result, provider) {
            const wanted = normalizeText(street.value);

            const text = normalizeText(
                [
                    result.display_name,
                    result.name,
                    result.type,
                    result.class,
                    result.properties?.name,
                    result.properties?.type,
                    result.properties?.osm_key,
                    result.properties?.osm_value
                ].filter(Boolean).join(' ')
            );

            let score = 0;

            if (wanted && text.includes(wanted)) {
                score += 150;
            }

            const tokens = wanted
                .split(' ')
                .filter(token => token.length >= 3);

            tokens.forEach(token => {
                if (text.includes(token)) {
                    score += 15;
                }
            });

            const poiWords = [
                'school',
                'college',
                'university',
                'hospital',
                'clinic',
                'barangay hall',
                'government',
                'police',
                'fire station',
                'market',
                'mall',
                'church',
                'chapel'
            ];

            poiWords.forEach(word => {
                if (text.includes(word)) {
                    score += 25;
                }
            });

            if (provider === 'photon') {
                score += 10;
            }

            return score;
        }

        // ============================================================
        // NOMINATIM
        // ============================================================

        async function searchNominatim(query) {
            const url =
                NOMINATIM_URL +
                '?' +
                new URLSearchParams({
                    q: query,
                    format: 'json',
                    addressdetails: '1',
                    limit: '5',
                    countrycodes: 'ph'
                });

            const results = await fetchJson(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            return results.map(result => ({
                ...result,
                provider: 'nominatim'
            }));
        }

        // ============================================================
        // PHOTON
        // ============================================================

        async function searchPhoton(query) {
            const url =
                PHOTON_URL +
                '?' +
                new URLSearchParams({
                    q: query,
                    limit: '8',
                    lang: 'en'
                });

            const response = await fetchJson(url);

            return (response.features || [])
                .filter(feature =>
                    feature.geometry &&
                    Array.isArray(feature.geometry.coordinates)
                )
                .map(feature => {
                    const p = feature.properties || {};
                    const c = feature.geometry.coordinates;

                    return {
                        provider: 'photon',
                        lat: c[1],
                        lon: c[0],
                        type: p.type || '',
                        class: p.osm_key || '',
                        properties: p,
                        display_name: [
                            p.name,
                            p.street,
                            p.district,
                            p.city,
                            p.state,
                            p.country
                        ].filter(Boolean).join(', ')
                    };
                });
        }

        function chooseBestResult(results) {
            if (!results.length) return null;

            results.forEach(result => {
                result._score = scoreResult(
                    result,
                    result.provider
                );
            });

            results.sort(
                (a, b) => b._score - a._score
            );

            console.log(
                'MAP CANDIDATES:',
                results.slice(0, 10).map(result => ({
                    score: result._score,
                    provider: result.provider,
                    name: result.display_name
                }))
            );

            return results[0];
        }

        // ============================================================
        // SEARCH LOCATION
        // ============================================================

        async function searchLocation() {
            if (!map || !marker) return;

            const queries = buildSearchQueries();

            if (!queries.length) {
                setStatus(
                    'Select a location first.',
                    'warning'
                );
                return;
            }

            const requestId = ++searchRequestId;

            setStatus(
                'Searching for the exact school / landmark...',
                'muted'
            );

            console.log(
                'MAP SEARCH QUERIES:',
                queries
            );

            let candidates = [];

            try {
                // Nominatim exact + shorter search.
                for (let i = 0; i < Math.min(queries.length, 2); i++) {
                    if (requestId !== searchRequestId) return;

                    try {
                        console.log(
                            'NOMINATIM SEARCH #' + (i + 1) + ':',
                            queries[i]
                        );

                        candidates.push(
                            ...(await searchNominatim(
                                queries[i]
                            ))
                        );

                        // Public Nominatim should not be hammered.
                        if (i === 0) {
                            await new Promise(
                                resolve => setTimeout(resolve, 1100)
                            );
                        }

                    } catch (error) {
                        console.warn(
                            'NOMINATIM SEARCH FAILED:',
                            error
                        );
                    }
                }

                if (requestId !== searchRequestId) return;

                // Photon is the important fallback for named POIs.
                try {
                    console.log(
                        'PHOTON SEARCH:',
                        queries[0]
                    );

                    candidates.push(
                        ...(await searchPhoton(
                            queries[0]
                        ))
                    );

                    if (
                        candidates.length === 0 &&
                        queries[1]
                    ) {
                        candidates.push(
                            ...(await searchPhoton(
                                queries[1]
                            ))
                        );
                    }

                } catch (error) {
                    console.warn(
                        'PHOTON SEARCH FAILED:',
                        error
                    );
                }

                if (requestId !== searchRequestId) return;

                // Remove duplicate coordinates.
                const unique = [];
                const seen = new Set();

                candidates.forEach(result => {
                    const lat = Number(result.lat);
                    const lng = Number(result.lon);

                    if (
                        !Number.isFinite(lat) ||
                        !Number.isFinite(lng)
                    ) {
                        return;
                    }

                    const key =
                        lat.toFixed(5) +
                        ',' +
                        lng.toFixed(5);

                    if (seen.has(key)) return;

                    seen.add(key);
                    unique.push(result);
                });

                const best = chooseBestResult(unique);

                // Exact or strong matching result.
                if (best) {
                    const lat = Number(best.lat);
                    const lng = Number(best.lon);

                    map.setView(
                        [lat, lng],
                        best._score >= 150 ? 18 : 16, {
                            animate: true
                        }
                    );

                    marker.setLatLng([lat, lng]);

                    setCoordinates(
                        lat,
                        lng
                    );

                    const name =
                        best.display_name ||
                        'Selected location';

                    marker.bindPopup(
                        '<strong>Incident Location</strong><br>' +
                        escapeHtml(name)
                    ).openPopup();

                    if (best._score >= 150) {
                        setStatus(
                            'Exact school / landmark found and pinned automatically.',
                            'success'
                        );
                    } else if (best._score >= 50) {
                        setStatus(
                            'Matching school / landmark found and pinned automatically.',
                            'success'
                        );
                    } else {
                        setStatus(
                            'Nearby matching location found. Please verify the marker.',
                            'warning'
                        );
                    }

                    console.log(
                        'MAP LOCATION FOUND:',
                        name
                    );

                    console.log(
                        'LAT:',
                        lat,
                        'LNG:',
                        lng
                    );

                    return;
                }

                // Final fallback: barangay area.
                const fallbackQuery = [
                    getSelectedText(barangay),
                    getSelectedText(city),
                    getSelectedText(province),
                    'Philippines'
                ].filter(Boolean).join(', ');

                if (fallbackQuery) {
                    try {
                        const fallbackResults =
                            await searchNominatim(
                                fallbackQuery
                            );

                        if (fallbackResults.length) {
                            const fallback =
                                fallbackResults[0];

                            const lat = Number(
                                fallback.lat
                            );

                            const lng = Number(
                                fallback.lon
                            );

                            if (
                                Number.isFinite(lat) &&
                                Number.isFinite(lng)
                            ) {
                                map.setView(
                                    [lat, lng],
                                    15, {
                                        animate: true
                                    }
                                );

                                marker.setLatLng([
                                    lat,
                                    lng
                                ]);

                                setCoordinates(
                                    lat,
                                    lng
                                );

                                marker.bindPopup(
                                    '<strong>Incident Area</strong><br>' +
                                    escapeHtml(
                                        fallback.display_name
                                    )
                                ).openPopup();

                                setStatus(
                                    'Exact landmark was not found. The barangay area was pinned instead.',
                                    'warning'
                                );

                                return;
                            }
                        }
                    } catch (error) {
                        console.warn(
                            'AREA FALLBACK FAILED:',
                            error
                        );
                    }
                }

                setStatus(
                    'Location was not found automatically. You may click the map only if needed.',
                    'warning'
                );

            } catch (error) {
                console.error(
                    'MAP SEARCH ERROR:',
                    error
                );

                setStatus(
                    'Map search failed. You can still select a point manually.',
                    'danger'
                );
            }
        }

        function debounceMapSearch() {
            clearTimeout(searchTimer);

            searchTimer = setTimeout(() => {
                if (
                    province.value ||
                    city.value ||
                    barangay.value ||
                    street.value.trim()
                ) {
                    searchLocation();
                }
            }, 1400);
        }

        // ============================================================
        // EVENTS
        // ============================================================

        province.addEventListener(
            'change',
            async function() {
                updateFullLocation();
                await loadCities(this.value);
                updateFullLocation();
                debounceMapSearch();
            }
        );

        city.addEventListener(
            'change',
            async function() {
                updateFullLocation();
                await loadBarangays(this.value);
                updateFullLocation();
                debounceMapSearch();
            }
        );

        barangay.addEventListener(
            'change',
            function() {
                updateFullLocation();
                debounceMapSearch();
            }
        );

        street.addEventListener(
            'input',
            function() {
                updateFullLocation();
                debounceMapSearch();
            }
        );

        street.addEventListener(
            'keydown',
            function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    updateFullLocation();
                    searchLocation();
                }
            }
        );

        searchLocationBtn.addEventListener(
            'click',
            function() {
                updateFullLocation();
                searchLocation();
            }
        );

        useMapPointBtn.addEventListener(
            'click',
            function() {
                if (!map || !marker) return;

                const position = marker.getLatLng();

                setCoordinates(
                    position.lat,
                    position.lng
                );

                setStatus(
                    'Current marker position saved as the incident coordinates.',
                    'success'
                );
            }
        );

        resetMapBtn.addEventListener(
            'click',
            function() {
                if (!map || !marker) return;

                map.setView(
                    [DEFAULT_LAT, DEFAULT_LNG],
                    DEFAULT_ZOOM
                );

                marker.setLatLng([
                    DEFAULT_LAT,
                    DEFAULT_LNG
                ]);

                setCoordinates(
                    DEFAULT_LAT,
                    DEFAULT_LNG
                );

                marker.bindPopup(
                    '<strong>Incident Location</strong><br>Cabanatuan City'
                ).closePopup();

                setStatus(
                    'Map reset to Cabanatuan City.',
                    'muted'
                );
            }
        );

        initializeMap();
        loadProvinces();

        console.log(
            'Improved incident location system initialized successfully.'
        );
    });
</script>

@endsection