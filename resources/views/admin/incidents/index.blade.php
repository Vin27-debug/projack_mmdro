@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-danger">Incident Command</h2>
        <p class="text-muted mb-0">Track active incidents and dispatch the nearest ambulance resources.</p>
    </div>
    <a href="{{ route('admin.incidents.create') }}" class="btn btn-danger">Create Incident</a>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incidents as $incident)
                    <tr>
                        <td class="fw-semibold">{{ $incident->incident_number }}</td>
                        <td>{{ $incident->reporter_name }}</td>
                        <td>{{ $incident->incident_type }}</td>
                        <td>{{ $incident->location }}</td>

                        <td>
                            @if($incident->priority == 'Critical')
                            <span class="badge bg-danger">
                                🔴 Critical
                            </span>
                            @elseif($incident->priority == 'High')
                            <span class="badge bg-warning text-dark">
                                🟠 High
                            </span>
                            @elseif($incident->priority == 'Medium')
                            <span class="badge bg-info text-dark">
                                🟡 Medium
                            </span>
                            @else
                            <span class="badge bg-success">
                                🟢 Low
                            </span>
                            @endif
                        </td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ ucfirst($incident->status) }}</span></td>
                        <td>
                            <a href="{{ route('admin.incidents.dispatch.form', $incident) }}" class="btn btn-sm btn-outline-danger">Dispatch</a>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No incidents recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection