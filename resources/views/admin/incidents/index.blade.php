@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <div>
        <h2 class="section-heading mb-1 text-danger">Incident Command</h2>
        <p class="section-excerpt mb-0">Search, manage, dispatch, and archive official disaster incident records.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.incidents.index', ['archived' => $showArchived ? 0 : 1]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-archive"></i> {{ $showArchived ? 'Active Records' : 'Archived Records' }}
        </a>
        @unless($showArchived)
        <a href="{{ route('admin.incidents.create') }}" class="btn btn-danger">Create Incident</a>
        @endunless
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.incidents.index') }}" class="row g-3 align-items-end">
            <input type="hidden" name="archived" value="{{ $showArchived ? 1 : 0 }}">
            <div class="col-lg-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Incident no., reporter, location">
            </div>
            <div class="col-lg-2">
                <label class="form-label">Incident Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    @foreach($incidentTypes as $type)
                    <option value="{{ $type }}" @selected(request('type')===$type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\Incident::VALID_STATUSES as $status)
                    <option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2"><label class="form-label">Start Date</label><input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control"></div>
            <div class="col-lg-2"><label class="form-label">End Date</label><input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control"></div>
            <div class="col-lg-1"><button class="btn btn-primary w-100">Filter</button></div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">{{ $showArchived ? 'Archived Incident Records' : 'Active Incident Records' }}</h5>
            <span class="badge bg-secondary">{{ $incidents->count() }} records</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Incident No</th>
                        <th>Reporter</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incidents as $incident)
                    <tr>
                        <td class="fw-semibold">{{ $incident->incident_number }}</td>
                        <td>{{ $incident->reporter_name }}</td>
                        <td>{{ $incident->incident_type }}</td>
                        <td>{{ collect([$incident->house_number, $incident->street, $incident->barangay, $incident->city, $incident->province])->filter()->implode(', ') ?: ($incident->location ?: 'N/A') }}</td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ ucfirst(str_replace('_', ' ', $incident->status)) }}</span></td>
                        <td>
                            @php($priorityClass = ['Critical'=>'danger','High'=>'warning','Medium'=>'info','Low'=>'success'][$incident->priority] ?? 'secondary')
                            <span class="badge bg-{{ $priorityClass }} {{ $priorityClass === 'warning' || $priorityClass === 'info' ? 'text-dark' : '' }}">{{ $incident->priority }}</span>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <a href="{{ route('admin.incidents.show', $incident) }}" class="btn btn-sm btn-outline-primary">View</a>
                                @if(!$incident->archived_at)
                                <a href="{{ route('admin.incidents.edit', $incident) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                @if(!in_array($incident->status, ['completed','closed','cancelled'], true))
                                <a href="{{ route('admin.incidents.dispatch.form', $incident) }}" class="btn btn-sm btn-outline-danger">Dispatch</a>
                                @endif
                                <form method="POST" action="{{ route('admin.incidents.archive', $incident) }}" onsubmit="return confirm('Archive this official incident record? It will remain searchable and will not be deleted.');">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-dark">Archive</button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('admin.incidents.restore', $incident) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">Restore</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No incident records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection