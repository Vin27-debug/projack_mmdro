@extends('layouts.driver')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Dispatch History</h2>
            <p class="text-muted mb-0">Search, filter and review past dispatches with timeline and response metrics.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="viewToggle" checked>
                <label class="form-check-label small text-muted" for="viewToggle">Table view</label>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input id="searchBox" type="search" class="form-control" placeholder="Search incident, vehicle, location...">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text">From</span>
                        <input id="dateFrom" type="date" class="form-control">
                        <span class="input-group-text">To</span>
                        <input id="dateTo" type="date" class="form-control">
                    </div>
                </div>

                <div class="col-md-3">
                    <select id="statusFilter" class="form-select">
                        <option value="">All Statuses</option>
                        @php
                        $statuses = ['pending','assigned','accepted','en_route','arrived','completed','cancelled'];
                        @endphp
                        @foreach($statuses as $s)
                        <option value="{{ $s }}">{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 text-end">
                    <button id="clearFilters" class="btn btn-outline-secondary">Clear</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div id="tableView" class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    @if($dispatches->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-clock-history fs-1 d-block mb-3"></i>
                        No dispatch history found yet.
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="dispatchTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:18%">Incident</th>
                                    <th style="width:18%">Vehicle</th>
                                    <th style="width:12%">Status</th>
                                    <th style="width:14%">Assigned</th>
                                    <th style="width:14%">Accepted</th>
                                    <th style="width:12%">Response</th>
                                    <th style="width:12%">Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dispatches as $dispatch)
                                @php
                                $incident = $dispatch->incident;
                                $vehicleName = $dispatch->vehicle?->vehicle_name ?? $dispatch->ambulance?->vehicle_name ?? 'N/A';
                                $assigned = $dispatch->assigned_at;
                                $accepted = $dispatch->accepted_at;
                                $completed = $dispatch->completed_at;
                                $status = $dispatch->status;
                                $assignedIso = $assigned?->toDateString() ?? '';
                                $responseMinutes = null;
                                if($accepted && $assigned){
                                $responseMinutes = $accepted->diffInMinutes($assigned);
                                } elseif($completed && $assigned){
                                $responseMinutes = $completed->diffInMinutes($assigned);
                                }

                                $statusClass = match($status){
                                'assigned'=> 'bg-warning text-dark',
                                'accepted'=> 'bg-primary text-white',
                                'en_route'=> 'bg-info text-dark',
                                'arrived'=> 'bg-success text-white',
                                'completed'=> 'bg-dark text-white',
                                'cancelled'=> 'bg-secondary text-white',
                                default => 'bg-light text-dark'
                                };
                                @endphp
                                <tr class="dispatch-row" data-incident="{{ $incident?->incident_number ?? '' }}" data-vehicle="{{ $vehicleName }}" data-status="{{ $status }}" data-assigned="{{ $assignedIso }}">
                                    <td>
                                        <div class="fw-semibold">{{ $incident?->incident_number ?? 'N/A' }}</div>
                                        <div class="small text-muted">{{ $incident?->location ?? 'No location' }}</div>
                                    </td>
                                    <td>{{ $vehicleName }}</td>
                                    <td>
                                        <span class="badge rounded-pill {{ $statusClass }}">{{ str_replace('_',' ', ucfirst($status)) }}</span>
                                    </td>
                                    <td>{{ $assigned?->format('M d, Y H:i') ?? '—' }}</td>
                                    <td>{{ $accepted?->format('M d, Y H:i') ?? '—' }}</td>
                                    <td>
                                        @if($responseMinutes !== null)
                                        <span class="fw-semibold">{{ $responseMinutes }} min</span>
                                        @else
                                        —
                                        @endif
                                    </td>
                                    <td>{{ $completed?->format('M d, Y H:i') ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div id="timelineView" class="card border-0 shadow-sm rounded-4 p-3">
                <h6 class="mb-3">Timeline</h6>
                @if($dispatches->isEmpty())
                <div class="text-center text-muted p-4">No timeline data</div>
                @else
                <div class="timeline">
                    @foreach($dispatches->sortByDesc(fn($d)=> $d->assigned_at) as $dispatch)
                    @php
                    $incident = $dispatch->incident;
                    $vehicleName = $dispatch->vehicle?->vehicle_name ?? $dispatch->ambulance?->vehicle_name ?? 'N/A';
                    $assigned = $dispatch->assigned_at;
                    $accepted = $dispatch->accepted_at;
                    $completed = $dispatch->completed_at;
                    $status = $dispatch->status;
                    $timeLabel = $assigned?->format('M d, Y H:i') ?? '';
                    $dotClass = match($status){
                    'arrived'=> 'bg-success',
                    'completed'=> 'bg-dark',
                    'cancelled'=> 'bg-secondary',
                    'accepted'=> 'bg-primary',
                    'assigned'=> 'bg-warning',
                    default => 'bg-info'
                    };
                    @endphp
                    <div class="timeline-item d-flex gap-3 mb-3">
                        <div class="timeline-marker">
                            <span class="rounded-circle d-inline-block {{ $dotClass }}" style="width:14px;height:14px;border:3px solid #fff;"></span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold">{{ $incident?->incident_number ?? 'N/A' }} — {{ $vehicleName }}</div>
                                    <div class="small text-muted">{{ $incident?->location ?? 'No location' }}</div>
                                </div>
                                <div class="small text-muted">{{ $timeLabel }}</div>
                            </div>
                            <div class="mt-1">
                                <span class="badge bg-light text-dark">{{ str_replace('_',' ', ucfirst($status)) }}</span>
                                @if($accepted && $assigned)
                                <span class="ms-2 small text-muted">Response: {{ $accepted->diffInMinutes($assigned) }} min</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    (function() {
        const searchBox = document.getElementById('searchBox');
        const dateFrom = document.getElementById('dateFrom');
        const dateTo = document.getElementById('dateTo');
        const statusFilter = document.getElementById('statusFilter');
        const clearBtn = document.getElementById('clearFilters');
        const rows = Array.from(document.querySelectorAll('.dispatch-row'));
        const viewToggle = document.getElementById('viewToggle');
        const tableView = document.getElementById('tableView');
        const timelineView = document.getElementById('timelineView');

        function matches(row) {
            const q = (searchBox.value || '').toLowerCase();
            const incident = (row.dataset.incident || '').toLowerCase();
            const vehicle = (row.dataset.vehicle || '').toLowerCase();
            const status = (row.dataset.status || '').toLowerCase();
            const assigned = row.dataset.assigned || '';

            if (q) {
                if (!incident.includes(q) && !vehicle.includes(q)) return false;
            }

            if (statusFilter.value) {
                if (status !== statusFilter.value) return false;
            }

            if (dateFrom.value) {
                if (!assigned || assigned < dateFrom.value) return false;
            }
            if (dateTo.value) {
                if (!assigned || assigned > dateTo.value) return false;
            }

            return true;
        }

        function applyFilters() {
            rows.forEach(r => {
                if (matches(r)) r.style.display = '';
                else r.style.display = 'none';
            });
        }

        [searchBox, dateFrom, dateTo, statusFilter].forEach(el => el?.addEventListener('input', applyFilters));
        clearBtn.addEventListener('click', function() {
            searchBox.value = '';
            dateFrom.value = '';
            dateTo.value = '';
            statusFilter.value = '';
            applyFilters();
        });

        viewToggle.addEventListener('change', function() {
            if (viewToggle.checked) {
                tableView.style.display = '';
                timelineView.style.display = 'block';
            } else {
                tableView.style.display = 'block';
                timelineView.style.display = 'none';
            }
        });

        // initialize
        viewToggle.checked = true;
        timelineView.style.display = 'block';
    })();
</script>
@endsection