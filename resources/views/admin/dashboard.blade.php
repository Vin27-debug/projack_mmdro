@extends('layouts.admin')

@section('content')
<style>
    .eoc-shell {
        max-width: 1760px;
        margin: 0 auto;
        color: #eef4ff;
    }

    .eoc-card {
        background: rgba(7, 18, 38, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.09);
        border-radius: 0.75rem;
    }

    .eoc-header {
        background: #0c2348;
        border-left: 3px solid #3b69ff;
    }

    .eoc-title {
        font-size: clamp(1.45rem, 2vw, 2.1rem);
        font-weight: 700;
        letter-spacing: 0;
    }

    .eoc-subtitle,
    .eoc-meta,
    .eoc-panel-subtitle {
        color: rgba(238, 244, 255, 0.62);
    }

    .eoc-section-title {
        font-size: 0.76rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: rgba(238, 244, 255, 0.62);
    }

    .eoc-kpi {
        min-height: 112px;
        border-top: 2px solid rgba(255, 255, 255, 0.12);
        padding: 1rem;
    }

    .eoc-kpi.critical {
        border-top-color: #dc3545;
    }

    .eoc-kpi.warning {
        border-top-color: #f0ad00;
    }

    .eoc-kpi.ready {
        border-top-color: #2aa876;
    }

    .eoc-kpi.info {
        border-top-color: #3b69ff;
    }

    .eoc-kpi-value {
        font-size: 1.9rem;
        font-weight: 700;
        line-height: 1;
    }

    .eoc-kpi-label {
        font-size: 0.74rem;
        color: rgba(238, 244, 255, 0.65);
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .eoc-kpi-note {
        font-size: 0.75rem;
        color: rgba(238, 244, 255, 0.5);
    }

    .eoc-seal {
        width: 58px;
        height: 58px;
        padding: 0.45rem;
        border-radius: 0.65rem;
        background: rgba(255, 255, 255, 0.08);
    }

    .eoc-seal img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .eoc-map-container {
        min-height: 480px;
        background: #10233c;
    }

    .advisory {
        border-left: 3px solid #f0ad00;
        padding: 0.85rem 0 0.85rem 0.9rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .advisory:last-child {
        border-bottom: 0;
    }

    .advisory.critical {
        border-left-color: #111827;
    }

    .advisory-priority {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .advisory-title {
        font-weight: 600;
        color: #fff;
    }

    .advisory-meta {
        font-size: 0.78rem;
        color: rgba(238, 244, 255, 0.6);
    }

    .progress {
        background: rgba(255, 255, 255, 0.1);
    }

    .status-dot {
        display: inline-block;
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 50%;
        margin-right: 0.35rem;
        background: #2aa876;
    }

    @media (max-width: 1199px) {
        .eoc-map-container {
            min-height: 390px;
        }
    }

    @media (max-width: 767px) {
        .eoc-map-container {
            min-height: 320px;
        }

        .eoc-header {
            padding: 1rem !important;
        }
    }
</style>

@php
$totalFleet = max(($ambulanceList ?? collect())->count(), 0);
$readyPercent = $totalFleet > 0 ? round((($availableVehicles ?? 0) / $totalFleet) * 100) : 0;
@endphp

<div class="container-fluid eoc-shell py-3 py-lg-4">
    <header class="eoc-card eoc-header p-3 p-lg-4 mb-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="eoc-seal"><img src="{{ asset('favicon.ico') }}" alt="MuniResQ logo"></div>
                <div>
                    <div class="eoc-section-title mb-1">MuniResQ / Municipal Emergency Operations Center</div>
                    <h1 class="eoc-title mb-1">Emergency Operations Dashboard</h1>
                    <p class="eoc-subtitle mb-0">Live situational awareness for municipal response coordination.</p>
                </div>
            </div>
            <div class="text-lg-end">
                <div class="badge bg-success-subtle text-success mb-1"><span class="status-dot"></span>System operational</div>
                <div class="eoc-meta small">{{ now()->format('F j, Y · H:i') }}</div>
            </div>
        </div>
    </header>

    <section class="mb-4" aria-labelledby="kpi-heading">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h2 id="kpi-heading" class="eoc-section-title mb-0">Current operational status</h2>
            <span class="eoc-meta small">Duty officer: {{ auth()->user()->name ?? 'Admin User' }}</span>
        </div>
        <div class="row g-2">
            <div class="col-6 col-xl-2">
                <div class="eoc-card eoc-kpi critical h-100">
                    <div class="eoc-kpi-label">Active incidents</div>
                    <div class="eoc-kpi-value mt-3" data-counter="activeIncidents">{{ $activeIncidents ?? 0 }}</div>
                    <div class="eoc-kpi-note mt-2">Open response cases</div>
                </div>
            </div>
            <div class="col-6 col-xl-2">
                <div class="eoc-card eoc-kpi info h-100">
                    <div class="eoc-kpi-label">Active dispatches</div>
                    <div class="eoc-kpi-value mt-3" data-counter="activeDispatches">{{ $activeDispatches ?? 0 }}</div>
                    <div class="eoc-kpi-note mt-2">Units assigned</div>
                </div>
            </div>
            <div class="col-6 col-xl-2">
                <div class="eoc-card eoc-kpi ready h-100">
                    <div class="eoc-kpi-label">Available units</div>
                    <div class="eoc-kpi-value mt-3" data-counter="availableVehicles">{{ $availableVehicles ?? 0 }}</div>
                    <div class="eoc-kpi-note mt-2">Ready for deployment</div>
                </div>
            </div>
            <div class="col-6 col-xl-2">
                <div class="eoc-card eoc-kpi ready h-100">
                    <div class="eoc-kpi-label">Fleet readiness</div>
                    <div class="eoc-kpi-value mt-3">{{ $readyPercent }}%</div>
                    <div class="eoc-kpi-note mt-2">{{ $maintenanceVehicles ?? 0 }} in maintenance</div>
                </div>
            </div>
            <div class="col-6 col-xl-2">
                <div class="eoc-card eoc-kpi warning h-100">
                    <div class="eoc-kpi-label">Avg response</div>
                    <div class="eoc-kpi-value mt-3">{{ $responseTime ?? 0 }}m</div>
                    <div class="eoc-kpi-note mt-2">Arrival time</div>
                </div>
            </div>
            <div class="col-6 col-xl-2">
                <div class="eoc-card eoc-kpi info h-100">
                    <div class="eoc-kpi-label">Notifications</div>
                    <div class="eoc-kpi-value mt-3">{{ $unreadNotifications ?? 0 }}</div>
                    <div class="eoc-kpi-note mt-2">Unread alerts</div>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-3 mb-4">
        <section class="col-xl-8" aria-labelledby="map-heading">
            <div class="eoc-card overflow-hidden h-100">
                <div class="p-3 border-bottom border-white border-opacity-10 d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <h2 id="map-heading" class="h5 mb-1">Live Operations Map</h2>
                        <p class="eoc-panel-subtitle small mb-0">Ambulances, drivers, and open incidents</p>
                    </div>
                    <span class="badge bg-primary-subtle text-info">Refreshes every 15s</span>
                </div>
                <div class="px-3 pt-2 small eoc-meta"><span class="text-success">● Available</span><span class="text-warning ms-3">● En route</span><span class="text-danger ms-3">● Incident</span></div>
                <div id="liveCommandMap" class="eoc-map-container mt-2"></div>
                <div class="px-3 pb-3 border-top border-white border-opacity-10">
                    <h3 class="h6 mt-3 mb-2">Vehicle GPS</h3>
                    <div id="liveVehicleList" class="small" aria-live="polite"></div>
                </div>
            </div>
        </section>
        <aside class="col-xl-4" aria-labelledby="advisory-heading">
            <div class="eoc-card h-100">
                <div class="p-3 border-bottom border-white border-opacity-10">
                    <h2 id="advisory-heading" class="h5 mb-1">Priority Advisories</h2>
                    <p class="eoc-panel-subtitle small mb-0">Open high-priority incidents requiring attention</p>
                </div>
                <div class="p-3">
                    @forelse(($priorityAdvisories ?? collect()) as $incident)
                    @php
                    $dispatch = $incident->dispatches->sortByDesc('created_at')->first();
                    $statusLabel = $incident->status === \App\Models\Incident::STATUS_PENDING ? 'Awaiting dispatch' : 'Unit assigned';
                    @endphp
                    <article class="advisory {{ strtolower((string) $incident->priority) === 'critical' ? 'critical' : '' }}">
                        <div class="d-flex justify-content-between gap-2"><span class="advisory-priority {{ strtolower((string) $incident->priority) === 'critical' ? 'text-light' : 'text-danger' }}">{{ \App\Models\Incident::priorityDisplayLabel($incident->priority) }}</span><span class="advisory-meta">{{ ($incident->call_received_at ?? $incident->created_at)?->diffForHumans() }}</span></div>
                        <div class="advisory-title mt-1">{{ $incident->incident_type }}</div>
                        <div class="advisory-meta">{{ $incident->formattedAddress() }}</div>
                        <div class="advisory-meta mt-1">Status: {{ $statusLabel }}{{ $dispatch?->status ? ' · '.ucwords(str_replace('_', ' ', $dispatch->status)) : '' }}</div>
                    </article>
                    @empty
                    <div class="eoc-meta py-4 text-center">No high-priority incidents at this time.</div>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>

    <section class="eoc-card mb-4" aria-labelledby="panic-alerts-heading">
        <div class="p-3 border-bottom border-white border-opacity-10">
            <h2 id="panic-alerts-heading" class="h5 mb-1">Active Panic Alerts</h2>
            <p class="eoc-panel-subtitle small mb-0">Emergency alerts requiring immediate attention</p>
        </div>
        <div class="p-3">
            @forelse(($activePanicAlerts ?? collect()) as $alert)
            <article class="d-flex justify-content-between align-items-start gap-3 py-2 border-bottom border-white border-opacity-10">
                <div>
                    <strong>{{ $alert->driver?->user?->name ?? 'Unknown Driver' }}</strong>
                    <div class="eoc-meta small">Panic alert triggered</div>
                </div>
                <div class="text-end small">
                    <div>{{ $alert->latitude }}, {{ $alert->longitude }}</div>
                    <div class="eoc-meta">{{ $alert->triggered_at?->diffForHumans() }}</div>
                </div>
            </article>
            @empty
            <div class="eoc-meta py-3">No active panic alerts at this time.</div>
            @endforelse
        </div>
    </section>

    <div class="row g-3">
        <section class="col-lg-7" aria-labelledby="fleet-heading">
            <div class="eoc-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 id="fleet-heading" class="h5 mb-1">Fleet Readiness</h2>
                        <p class="eoc-panel-subtitle small mb-0">Current unit availability for deployment</p>
                    </div><a href="{{ route('admin.ambulances.index') }}" class="btn btn-sm btn-outline-light">View fleet <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="d-flex justify-content-between small mb-2"><span class="eoc-meta">Available units</span><strong>{{ $availableVehicles ?? 0 }} / {{ $totalFleet }}</strong></div>
                <div class="progress mb-3" style="height: .55rem">
                    <div class="progress-bar bg-success" style="width: {{ $readyPercent }}%" aria-label="{{ $readyPercent }} percent fleet readiness"></div>
                </div>
                <div class="row g-2 small">
                    <div class="col-4"><span class="text-success">Available</span><strong class="d-block fs-5">{{ $availableVehicles ?? 0 }}</strong></div>
                    <div class="col-4"><span class="text-warning">Responding</span><strong class="d-block fs-5">{{ $activeDispatches ?? 0 }}</strong></div>
                    <div class="col-4"><span class="text-danger">Maintenance</span><strong class="d-block fs-5">{{ $maintenanceVehicles ?? 0 }}</strong></div>
                </div>
            </div>
        </section>
        <section class="col-lg-5" aria-labelledby="operations-heading">
            <div class="eoc-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h2 id="operations-heading" class="h5 mb-1">Current Operations</h2>
                        <p class="eoc-panel-subtitle small mb-0">Latest command activity</p>
                    </div><a href="{{ route('admin.audit-logs.index') }}" class="eoc-meta small">Audit log</a>
                </div>
                @forelse(($recentActivities ?? collect())->take(4) as $activity)<div class="d-flex justify-content-between gap-3 py-2 border-bottom border-white border-opacity-10 small"><span>{{ $activity->action ?? 'System update' }}</span><span class="eoc-meta text-nowrap">{{ $activity->created_at?->diffForHumans() }}</span></div>@empty<div class="eoc-meta py-3">No recent activity recorded.</div>@endforelse
            </div>
        </section>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    let liveMapInstance = null;
    let liveMarkerLayer = null;
    let mapRequest = null;
    let mapHasFitted = false;
    const GPS_FRESH_SECONDS = @json((int) config('services.muniresq.location_fresh_seconds', 60));
    const GPS_STALE_LIMIT_SECONDS = @json((int) config('services.muniresq.location_stale_limit_minutes', 5) * 60);

    document.addEventListener('DOMContentLoaded', initializeLiveCommandMap);

    function initializeLiveCommandMap() {
        const mapContainer = document.getElementById('liveCommandMap');
        if (!mapContainer || liveMapInstance) return;
        liveMapInstance = L.map(mapContainer, {
            scrollWheelZoom: false
        }).setView([15.4866, 120.9675], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(liveMapInstance);
        liveMarkerLayer = L.layerGroup().addTo(liveMapInstance);
        loadMapData();
        window.setInterval(loadMapData, 15000);
    }

    function loadMapData() {
        if (!liveMapInstance || document.hidden || mapRequest) return;
        mapRequest = new AbortController();
        fetch("{{ route('admin.dashboard.live-command-map') }}", {
                headers: {
                    Accept: 'application/json'
                },
                cache: 'no-store',
                signal: mapRequest.signal
            })
            .then(response => response.ok ? response.json() : Promise.reject(new Error('Map request failed')))
            .then(data => {
                liveMarkerLayer.clearLayers();
                renderVehicleList(data.ambulances || []);
                const markers = [];
                (data.ambulances || []).concat(data.drivers || []).forEach(item => addMarker(item, item.type === 'driver' ? 6 : 8, getStatusColor(item.status_key)) && markers.push([item.latitude, item.longitude]));
                (data.incidents || []).forEach(item => addIncidentMarker(item) && markers.push([item.latitude, item.longitude]));
                if (markers.length && !mapHasFitted) {
                    liveMapInstance.fitBounds(L.latLngBounds(markers).pad(0.15));
                    mapHasFitted = true;
                }
            })
            .catch(() => {})
            .finally(() => {
                mapRequest = null;
            });
    }

    function getGpsFreshness(item) {
        if (!item.recorded_at) return {
            status: 'missing',
            relative: 'No GPS data'
        };

        const recordedAt = Date.parse(item.recorded_at);
        if (!Number.isFinite(recordedAt)) return {
            status: 'missing',
            relative: 'No GPS data'
        };

        const ageSeconds = Math.max(0, Math.floor((Date.now() - recordedAt) / 1000));
        const relative = ageSeconds < 10 ? 'just now' : ageSeconds < 60 ? `${ageSeconds} sec ago` : ageSeconds < 3600 ? `${Math.floor(ageSeconds / 60)} min ago` : `${Math.floor(ageSeconds / 3600)} hr ago`;
        const status = ageSeconds < GPS_FRESH_SECONDS ? 'fresh' : ageSeconds <= GPS_STALE_LIMIT_SECONDS ? 'delayed' : 'stale';

        return {
            status,
            relative
        };
    }

    function gpsFreshnessMarkup(item) {
        const freshness = getGpsFreshness(item);
        if (freshness.status === 'missing') return '<span class="text-muted">No GPS data</span>';

        const badgeClass = freshness.status === 'fresh' ? 'bg-success-subtle text-success' : freshness.status === 'delayed' ? 'bg-warning text-dark' : 'bg-danger-subtle text-danger';
        const badgeLabel = freshness.status.charAt(0).toUpperCase() + freshness.status.slice(1);
        const outdated = freshness.status === 'stale' ? '<span class="text-danger ms-1">Location may be outdated</span>' : '';
        const hasAccuracy = item.accuracy_meters !== null && item.accuracy_meters !== undefined && item.accuracy_meters !== '';
        const accuracy = hasAccuracy ? Number(item.accuracy_meters) : NaN;
        const accuracyLabel = Number.isFinite(accuracy) ? ` <span class="text-muted">±${accuracy.toFixed(0)} m</span>` : '';

        return `<span class="badge ${badgeClass}">${badgeLabel}</span> <span>${freshness.relative}</span>${outdated}${accuracyLabel}`;
    }

    function renderVehicleList(vehicles) {
        const list = document.getElementById('liveVehicleList');
        if (!list) return;
        if (!vehicles.length) {
            list.innerHTML = '<span class="eoc-meta">No vehicles configured.</span>';
            return;
        }

        list.innerHTML = vehicles.map(item => `
            <div class="d-flex justify-content-between align-items-start gap-3 py-2 border-bottom border-white border-opacity-10">
                <div><strong>${escapePopupText(item.name || 'Ambulance')}</strong><div class="eoc-meta">${escapePopupText(item.driver_name || 'Unassigned')}</div></div>
                <div class="text-end">${gpsFreshnessMarkup(item)}</div>
            </div>
        `).join('');
    }

    function addMarker(item, radius, color) {
        if (item.latitude == null || item.longitude == null) return false;
        if (!Number.isFinite(Number(item.latitude)) || !Number.isFinite(Number(item.longitude))) return false;
        L.circleMarker([item.latitude, item.longitude], {
            radius,
            fillColor: color,
            color: '#fff',
            weight: 1.5,
            fillOpacity: 0.9
        }).bindPopup(`<strong>${escapePopupText(item.name || item.driver_name || 'Unit')}</strong><br>Status: ${escapePopupText(item.status || 'Unknown')}<br>GPS: ${gpsFreshnessMarkup(item)}`).addTo(liveMarkerLayer);
        return true;
    }

    function addIncidentMarker(item) {
        if (!Number.isFinite(Number(item.latitude)) || !Number.isFinite(Number(item.longitude))) return false;
        L.circleMarker([item.latitude, item.longitude], {
            radius: 9,
            fillColor: '#dc3545',
            color: '#fff',
            weight: 1.5,
            fillOpacity: 0.95
        }).bindPopup(`<strong>${escapePopupText(item.incident_number || 'Incident')}</strong><br>${escapePopupText(item.type || 'Emergency')}<br>${escapePopupText(item.address || item.location || 'Address unavailable')}`).addTo(liveMarkerLayer);
        return true;
    }

    function getStatusColor(status) {
        return status === 'en_route' ? '#f0ad00' : status === 'emergency' ? '#dc3545' : '#2aa876';
    }

    function escapePopupText(value) {
        return String(value ?? 'Unknown').replace(/[&<>'\"]/g, character => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        } [character]));
    }
</script>
@endsection