@extends('layouts.admin')

@section('content')

<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
<div class="container">

    <h1 class="mb-4">Create Incident</h1>

    <form method="POST"
        action="{{ route('admin.incidents.store') }}">

        @csrf

        <div class="mb-3">
            <label class="form-label">
                Reporter Name
            </label>

            <input type="text"
                name="reporter_name"
                class="form-control"
                required>
        </div>

        <div class="mb-3">
            <label class="form-label">
                Contact Number
            </label>

            <input type="text"
                name="contact_number"
                class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">
                Incident Type
            </label>

            <input type="text"
                name="incident_type"
                class="form-control"
                required>
        </div>

        <div class="mb-3">
            <label class="form-label">
                Priority
            </label>

            <select name="priority" class="form-select">
                <option value="Low">🟢 Low</option>
                <option value="Medium" selected>🟡 Medium</option>
                <option value="High">🟠 High</option>
                <option value="Critical">🔴 Critical</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">
                Location Description
            </label>

            <input type="text"
                name="location"
                class="form-control"
                placeholder="Example: Brgy. Mabini, Cabanatuan City">
        </div>

        <div class="mb-3">
            <label class="form-label">
                Description
            </label>

            <textarea name="description"
                rows="4"
                class="form-control"></textarea>
        </div>

        <div class="mb-3">

            <label class="form-label">
                Select Incident Location On Map
            </label>

            <div id="map"
                style="height:400px;border-radius:10px;"></div>

            <small class="text-muted">
                Click anywhere on the map to set the incident location.
            </small>

        </div>

        <div class="row">

            <div class="mb-3">
                <label>Latitude</label>
                <input type="hidden"
                    id="latitude"
                    name="latitude"
                    value="15.425">
                <input type="text"
                    id="latitude_display"
                    class="form-control"
                    value="15.425"
                    readonly>
            </div>

            <div class="mb-3">
                <label>Longitude</label>
                <input type="hidden"
                    id="longitude"
                    name="longitude"
                    value="120.850">
                <input type="text"
                    id="longitude_display"
                    class="form-control"
                    value="120.850"
                    readonly>
            </div>

        </div>

        <div class="mt-4">

            <button class="btn btn-success">
                Save Incident
            </button>

        </div>

    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const map = L.map('map').setView(
        [15.4866, 120.9668],
        13
    );

    L.tileLayer(
        'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }
    ).addTo(map);

    let marker = null;

    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const latitudeDisplay = document.getElementById('latitude_display');
    const longitudeDisplay = document.getElementById('longitude_display');

    const syncCoordinateFields = (lat, lng) => {
        latitudeInput.value = lat.toFixed(7);
        longitudeInput.value = lng.toFixed(7);
        latitudeDisplay.value = lat.toFixed(7);
        longitudeDisplay.value = lng.toFixed(7);
    };

    map.on('click', function(e) {

        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        if (marker) {
            map.removeLayer(marker);
        }

        marker = L.marker([
            lat,
            lng
        ]).addTo(map);

        syncCoordinateFields(lat, lng);
    });
</script>

@endsection