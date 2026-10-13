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
        color: rgba(238, 244, 255, 0.76);
    }

    .eoc-section-title {
        font-size: 0.875rem;
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
        font-size: 0.875rem;
        color: rgba(238, 244, 255, 0.65);
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .eoc-kpi-note {
        font-size: 0.875rem;
        color: rgba(238, 244, 255, 0.72);
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
        border-left-color: #dc3545;
    }

    .advisory-priority {
        font-size: 0.875rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .advisory-title {
        font-weight: 600;
        color: #fff;
    }

    .advisory-meta {
        font-size: 0.875rem;
        color: rgba(238, 244, 255, 0.76);
    }

    .incident-priority-critical {
        border-left: 3px solid #dc3545;
    }

    .incident-priority-high {
        border-left: 3px solid #f0ad00;
    }

    .status-count-card {
        min-height: 76px;
        padding: .8rem 1rem;
        border: 1px solid rgba(255, 255, 255, .1);
        border-radius: .65rem;
        background: rgba(255, 255, 255, .035);
    }

    .status-count-label {
        color: rgba(238, 244, 255, .78);
        font-size: .875rem;
    }

    .status-count-value {
        color: #fff;
        font-size: 1.25rem;
        font-weight: 700;
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
$totalFleet = max((int) ($totalFleet ?? 0), 0);
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
                <div class="eoc-meta mb-1">Operational data snapshot</div>
                <div class="eoc-meta small">{{ now()->format('F j, Y · H:i') }}</div>
            </div>
        </div>
    </header>

    <section class="d-flex flex-wrap gap-2 mb-4" aria-label="Command actions">
        <a href="{{ route('admin.incidents.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Create incident</a>
        <a href="{{ route('dispatch.center') }}" class="btn btn-outline-light"><i class="bi bi-send me-1" aria-hidden="true"></i>Dispatch / assign unit</a>
        <a href="#liveCommandMap" class="btn btn-outline-light"><i class="bi bi-map me-1" aria-hidden="true"></i>Open live command map</a>
        <a href="{{ auth()->user()->hasRole('super-admin') ? route('superadmin.incidents.index') : route('admin.incidents.index') }}" class="btn btn-outline-light"><i class="bi bi-list-ul me-1" aria-hidden="true"></i>View active incidents</a>
    </section>

    <section class="mb-4" aria-labelledby="kpi-heading">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h2 id="kpi-heading" class="eoc-section-title mb-0">Current operational status</h2>
            <span class="eoc-meta small">Duty officer: {{ auth()->user()->name ?? 'Admin User' }}</span>
        </div>
        <div class="row g-2">
            <div class="col-6 col-xl-3">
                <div class="eoc-card eoc-kpi critical h-100">
                    <div class="eoc-kpi-label">Active incidents</div>
                    <div class="eoc-kpi-value mt-3" data-counter="activeIncidents">{{ $activeIncidents ?? 0 }}</div>
                    <div class="eoc-kpi-note mt-2">Open response cases</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="eoc-card eoc-kpi info h-100">
                    <div class="eoc-kpi-label">Active dispatches</div>
                    <div class="eoc-kpi-value mt-3" data-counter="activeDispatches">{{ $activeDispatches ?? 0 }}</div>
                    <div class="eoc-kpi-note mt-2">Units assigned</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="eoc-card eoc-kpi ready h-100">
                    <div class="eoc-kpi-label">Available units</div>
                    <div class="eoc-kpi-value mt-3" data-counter="availableVehicles">{{ $availableVehicles ?? 0 }}</div>
                    <div class="eoc-kpi-note mt-2">Ready for deployment</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="eoc-card eoc-kpi ready h-100">
                    <div class="eoc-kpi-label">Fleet readiness</div>
                    <div class="eoc-kpi-value mt-3">{{ $readyPercent }}%</div>
                    <div class="eoc-kpi-note mt-2">{{ $maintenanceVehicles ?? 0 }} in maintenance</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="eoc-card eoc-kpi warning h-100">
                    <div class="eoc-kpi-label">Avg response</div>
                    <div class="eoc-kpi-value mt-3">{{ $responseTime ?? 0 }}m</div>
                    <div class="eoc-kpi-note mt-2">Arrival time</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="eoc-card eoc-kpi info h-100">
                    <div class="eoc-kpi-label">High / critical incidents</div>
                    <div class="eoc-kpi-value mt-3" data-counter="priorityIncidents">{{ $priorityIncidents ?? 0 }}</div>
                    <div class="eoc-kpi-note mt-2">Open priority cases</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="eoc-card eoc-kpi info h-100">
                    <div class="eoc-kpi-label">Eligible drivers</div>
                    <div class="eoc-kpi-value mt-3">{{ $availableDrivers ?? 0 }}</div>
                    <div class="eoc-kpi-note mt-2">Approved and available</div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4" aria-labelledby="incident-status-counts-heading">
        <h2 id="incident-status-counts-heading" class="eoc-section-title mb-2">Incident counts by status</h2>
        <div class="row g-2">
            @foreach([
                'pending' => ['label' => 'Pending', 'tone' => 'warning'],
                'dispatched' => ['label' => 'Dispatched', 'tone' => 'info'],
                'responding' => ['label' => 'Responding', 'tone' => 'danger'],
                'completed' => ['label' => 'Completed', 'tone' => 'success'],
                'closed' => ['label' => 'Closed', 'tone' => 'secondary'],
                'cancelled' => ['label' => 'Cancelled', 'tone' => 'secondary'],
            ] as $status => $statusMeta)
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="status-count-card" data-status-count="{{ $status }}">
                        <div class="status-count-label">{{ $statusMeta['label'] }}</div>
                        <div class="status-count-value text-{{ $statusMeta['tone'] }}">{{ (int) ($incidentStatusCounts[$status] ?? 0) }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="eoc-card mb-4" aria-labelledby="recent-incidents-heading">
        <div class="p-3 border-bottom border-white border-opacity-10 d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div>
                <h2 id="recent-incidents-heading" class="h5 mb-1">Recent Incidents</h2>
                <p class="eoc-panel-subtitle small mb-0">Latest active incident records</p>
            </div>
            <a class="btn btn-sm btn-outline-light" href="{{ route('admin.incidents.index') }}">
                View all <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead><tr><th scope="col">ID</th><th scope="col">Incident</th><th scope="col">Type</th><th scope="col">Location</th><th scope="col">Priority</th><th scope="col">Status</th><th scope="col">Elapsed</th><th scope="col">Details</th></tr></thead>
                <tbody>
                    @forelse(($recentIncidents ?? collect()) as $incident)
                    @php
                    $priorityKey = strtolower((string) $incident->priority);
                    @endphp
                    <tr class="{{ $priorityKey === 'critical' ? 'incident-priority-critical' : ($priorityKey === 'high' ? 'incident-priority-high' : '') }}">
                        <td>#{{ $incident->id }}</td>
                        <td>{{ $incident->incident_number }}</td>
                        <td>{{ $incident->incident_type }}</td>
                        <td>{{ $incident->formattedAddress() }}</td>
                        <td><span class="badge bg-{{ $priorityKey === 'critical' ? 'danger' : ($priorityKey === 'high' ? 'warning text-dark' : ($priorityKey === 'medium' ? 'info text-dark' : 'success')) }}">{{ $incident->priority ?: 'Not set' }}</span></td>
                        <td>{{ ucfirst(str_replace('_', ' ', $incident->status)) }}</td>
                        <td>{{ ($incident->call_received_at ?? $incident->created_at)?->diffForHumans() ?? 'Unknown' }}</td>
                        <td><a class="btn btn-sm btn-outline-light" href="{{ route('admin.incidents.show', $incident) }}">View details</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center eoc-meta py-4">No active incidents recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
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
                <div id="mapConnectionStatus" class="px-3 pt-2 small eoc-meta" role="status" aria-live="polite">Loading current locations…</div>
                <div class="px-3 pt-2 small eoc-meta"><span class="text-success">● Available</span><span class="text-warning ms-3">● En route</span><span class="text-danger ms-3">● Incident</span><span class="text-warning ms-3">● Vulnerable area</span></div>
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
                    <h2 id="advisory-heading" class="h5 mb-1">Priority Incident Queue</h2>
                    <p class="eoc-panel-subtitle small mb-0">Open high-priority incidents requiring attention</p>
                </div>
                <div class="p-3">
                    @forelse(($priorityAdvisories ?? collect()) as $incident)
                    @php
                    $dispatch = $incident->dispatches->first();
                    $statusLabel = $dispatch ? 'Unit assigned' : 'Awaiting dispatch';
                    @endphp
                    <article class="advisory {{ strtolower((string) $incident->priority) === 'critical' ? 'critical' : '' }}">
                        <div class="d-flex justify-content-between gap-2"><span class="advisory-priority {{ strtolower((string) $incident->priority) === 'critical' ? 'text-light' : 'text-danger' }}">{{ \App\Models\Incident::priorityDisplayLabel($incident->priority) }}</span><span class="advisory-meta">{{ ($incident->call_received_at ?? $incident->created_at)?->diffForHumans() }}</span></div>
                        <div class="advisory-title mt-1">{{ $incident->incident_type }}</div>
                        <div class="advisory-meta">{{ $incident->incident_number }} · {{ ucfirst($incident->status) }}</div>
                        <div class="advisory-meta">{{ $incident->formattedAddress() }}</div>
                        <div class="advisory-meta mt-1">Status: {{ $statusLabel }}{{ $dispatch?->status ? ' · '.ucwords(str_replace('_', ' ', $dispatch->status)) : '' }}</div>
                        <a class="btn btn-sm btn-outline-light mt-2" href="{{ route('admin.incidents.show', $incident) }}" aria-label="Open incident {{ $incident->incident_number }}">Open incident</a>
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

    <section class="eoc-card p-3 mb-4" aria-labelledby="dispatches-heading">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 id="dispatches-heading" class="h5 mb-1">Active Dispatches</h2>
                <p class="eoc-panel-subtitle small mb-0">Current assignments and response state</p>
            </div>
            <a href="{{ route('dispatch.center') }}" class="btn btn-sm btn-outline-light">Dispatch center</a>
        </div>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead><tr><th scope="col">Incident</th><th scope="col">Vehicle</th><th scope="col">Driver</th><th scope="col">Status</th><th scope="col">Updated</th></tr></thead>
                <tbody>
                    @forelse(($dispatchList ?? collect()) as $dispatch)
                    <tr>
                        <td>{{ $dispatch->incident?->incident_number ?? 'Incident unavailable' }}</td>
                        <td>{{ $dispatch->vehicle?->vehicle_name ?? $dispatch->vehicle?->plate_number ?? 'Unassigned' }}</td>
                        <td>{{ $dispatch->driver?->user?->name ?? 'Unassigned' }}</td>
                        <td><span class="badge bg-info-subtle text-info">{{ ucfirst(str_replace('_', ' ', $dispatch->status)) }}</span></td>
                        <td>{{ $dispatch->updated_at?->diffForHumans() ?? 'Unknown' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center eoc-meta py-4">No active dispatches.</td></tr>
                    @endforelse
                </tbody>
            </table>
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
                @if($totalFleet > 0)
                <div class="d-flex justify-content-between small mb-2" data-fleet-total="{{ $totalFleet }}"><span class="eoc-meta">Available units</span><strong>{{ $availableVehicles ?? 0 }} / {{ $totalFleet }}</strong></div>
                <div class="progress mb-3" style="height: .55rem">
                    <div class="progress-bar bg-success" style="width: {{ $readyPercent }}%" aria-label="{{ $readyPercent }} percent fleet readiness"></div>
                </div>
                <div class="row g-2 small">
                    <div class="col-4"><span class="text-success">Available</span><strong class="d-block fs-5" data-fleet-available="{{ $availableVehicles ?? 0 }}">{{ $availableVehicles ?? 0 }}</strong></div>
                    <div class="col-4"><span class="text-warning">Responding</span><strong class="d-block fs-5" data-fleet-responding="{{ $respondingVehicles ?? 0 }}">{{ $respondingVehicles ?? 0 }}</strong></div>
                    <div class="col-4"><span class="text-danger">Maintenance</span><strong class="d-block fs-5" data-fleet-maintenance="{{ $maintenanceVehicles ?? 0 }}">{{ $maintenanceVehicles ?? 0 }}</strong></div>
                </div>
                @else
                <div class="eoc-meta py-4 text-center" role="status">No active vehicles are registered. Fleet readiness counts will appear when vehicles are added.</div>
                @endif
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
    const GPS_STALE_LIMIT_SECONDS = 180;
    let serverClockOffsetMs = 0;

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
                const serverTime = Date.parse(data.generated_at || '');
                if (Number.isFinite(serverTime)) {
                    serverClockOffsetMs = serverTime - Date.now();
                }
                liveMarkerLayer.clearLayers();
                renderVehicleList(data.ambulances || []);
                const markers = [];
                (data.ambulances || []).concat(data.drivers || []).forEach(item => addMarker(item, item.type === 'driver' ? 6 : 8, getStatusColor(item.status_key)) && markers.push([item.latitude, item.longitude]));
                (data.incidents || []).forEach(item => addIncidentMarker(item) && markers.push([item.latitude, item.longitude]));
                (data.vulnerable_areas || []).forEach(item => addVulnerableAreaMarker(item) && markers.push([item.latitude, item.longitude]));
                if (markers.length && !mapHasFitted) {
                    liveMapInstance.fitBounds(L.latLngBounds(markers).pad(0.15));
                    mapHasFitted = true;
                }
                const connectionStatus = document.getElementById('mapConnectionStatus');
                if (connectionStatus) connectionStatus.textContent = locationFeedStatus(data, serverTime);
            })
            .catch(() => {
                const connectionStatus = document.getElementById('mapConnectionStatus');
                if (connectionStatus) connectionStatus.textContent = 'Location feed unavailable. Any markers shown may be out of date.';
            })
            .finally(() => {
                mapRequest = null;
            });
    }

    function getGpsFreshness(item) {
        if (!item.recorded_at) return {
            status: item.gps_status === 'invalid' ? 'invalid' : 'missing',
            relative: item.gps_status === 'invalid' ? 'GPS timestamp unavailable' : 'No GPS data recorded'
        };

        const recordedAt = Date.parse(item.recorded_at);
        if (!Number.isFinite(recordedAt)) return {
            status: 'invalid',
            relative: 'GPS timestamp unavailable'
        };

        const currentTime = Date.now() + serverClockOffsetMs;
        if (recordedAt > currentTime) return {
            status: 'invalid',
            relative: 'GPS timestamp unavailable'
        };

        const ageSeconds = Math.floor((currentTime - recordedAt) / 1000);
        const minutes = Math.floor(ageSeconds / 60);
        const hours = Math.floor(ageSeconds / 3600);
        const relative = ageSeconds < 10 ? 'Last updated just now' : ageSeconds < 60 ? `Last updated ${ageSeconds} seconds ago` : ageSeconds < 3600 ? `Last updated ${minutes} ${minutes === 1 ? 'minute' : 'minutes'} ago` : `Last updated ${hours} ${hours === 1 ? 'hour' : 'hours'} ago`;
        const status = ageSeconds > GPS_STALE_LIMIT_SECONDS ? 'stale' : ageSeconds < GPS_FRESH_SECONDS ? 'fresh' : 'delayed';

        return {
            status,
            relative
        };
    }

    function locationFeedStatus(data, serverTime) {
        const trackedUnits = (data.ambulances || []).concat(data.drivers || []);
        const counts = { stale: 0, missing: 0, delayed: 0, invalid: 0 };
        trackedUnits.forEach(function (item) {
            const status = getGpsFreshness(item).status;
            if (Object.prototype.hasOwnProperty.call(counts, status)) counts[status]++;
        });

        const warnings = [];
        if (counts.stale) warnings.push(counts.stale + ' stale');
        if (counts.missing) warnings.push(counts.missing + ' missing');
        if (counts.delayed) warnings.push(counts.delayed + ' delayed');
        if (counts.invalid) warnings.push(counts.invalid + ' invalid');
        const updatedAt = new Date(serverTime || Date.now()).toLocaleTimeString();

        if (!trackedUnits.length) return `Location feed updated ${updatedAt}. No vehicle or driver GPS records are available.`;
        return warnings.length
            ? `Location feed updated ${updatedAt}. GPS attention needed: ${warnings.join(', ')}.`
            : `Location feed updated ${updatedAt}. All reported GPS locations are fresh.`;
    }

    function gpsFreshnessMarkup(item) {
        const freshness = getGpsFreshness(item);
        if (freshness.status === 'missing') return '<span class="badge bg-warning text-dark" role="status">No GPS data recorded</span>';
        if (freshness.status === 'invalid') return '<span class="badge bg-warning text-dark">GPS timestamp unavailable</span>';

        const badgeClass = freshness.status === 'fresh' ? 'bg-success-subtle text-success' : 'bg-warning text-dark';
        const badgeLabel = freshness.status === 'fresh' ? 'GPS ACTIVE' : freshness.status === 'delayed' ? 'DELAYED' : '';
        const outdated = freshness.status === 'stale' ? '<span class="badge bg-warning text-dark d-inline-block text-start text-wrap mt-1" role="status"><i class="bi bi-exclamation-triangle-fill me-1" aria-hidden="true"></i>GPS LOCATION STALE — Tracking may be outdated.</span>' : '';
        const hasAccuracy = item.accuracy_meters !== null && item.accuracy_meters !== undefined && item.accuracy_meters !== '';
        const accuracy = hasAccuracy ? Number(item.accuracy_meters) : NaN;
        const accuracyLabel = Number.isFinite(accuracy) ? ` <span class="text-muted">±${accuracy.toFixed(0)} m</span>` : '';

        const badge = badgeLabel ? `<span class="badge ${badgeClass}">${badgeLabel}</span> ` : '';
        return `${badge}<span>${freshness.relative}</span>${outdated}${accuracyLabel}`;
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
        if (getGpsFreshness(item).status === 'invalid') return false;
        const latitude = Number(item.latitude);
        const longitude = Number(item.longitude);
        if (item.latitude == null || item.longitude == null || !Number.isFinite(latitude) || !Number.isFinite(longitude) || latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) return false;
        L.circleMarker([latitude, longitude], {
            radius,
            fillColor: color,
            color: '#fff',
            weight: 1.5,
            fillOpacity: 0.9
        }).bindPopup(`<strong>${escapePopupText(item.name || item.driver_name || 'Unit')}</strong><br>Status: ${escapePopupText(item.status || 'Unknown')}<br>GPS: ${gpsFreshnessMarkup(item)}`).addTo(liveMarkerLayer);
        return true;
    }

    function addIncidentMarker(item) {
        const latitude = Number(item.latitude);
        const longitude = Number(item.longitude);
        if (!Number.isFinite(latitude) || !Number.isFinite(longitude) || latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) return false;
        L.circleMarker([latitude, longitude], {
            radius: 9,
            fillColor: '#dc3545',
            color: '#fff',
            weight: 1.5,
            fillOpacity: 0.95
        }).bindPopup(`<strong>${escapePopupText(item.incident_number || 'Incident')}</strong><br>${escapePopupText(item.type || 'Emergency')}<br>${escapePopupText(item.address || item.location || 'Address unavailable')}`).addTo(liveMarkerLayer);
        return true;
    }

    function addVulnerableAreaMarker(item) {
        const latitude = Number(item.latitude);
        const longitude = Number(item.longitude);
        if (!Number.isFinite(latitude) || !Number.isFinite(longitude) || latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) return false;

        const level = String(item.vulnerability_level || 'Unclassified');
        const color = level.toLowerCase() === 'critical' ? '#dc3545' : level.toLowerCase() === 'high' ? '#f0ad00' : '#8b7cf6';
        L.circleMarker([latitude, longitude], {
            radius: 10,
            fillColor: color,
            color: '#fff',
            weight: 2,
            dashArray: '4 3',
            fillOpacity: 0.5
        }).bindPopup(`<strong>Vulnerable area: ${escapePopupText(item.name || 'Area')}</strong><br>Risk level: ${escapePopupText(level)}<br>${escapePopupText(item.area_type || 'Community')}<br>${escapePopupText(item.address || 'Address unavailable')}`).addTo(liveMarkerLayer);
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
