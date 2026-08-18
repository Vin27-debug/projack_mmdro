@extends('layouts.superadmin')

@section('content')
<div class="page-header">
    <div>
        <div class="small text-uppercase text-white-50 mb-2">Driver Operations</div>
        <h1 class="page-title">Driver Dashboard</h1>
        <p class="page-subtitle mb-0">Track assigned incidents and current driver assignments in one place.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-5">
        <div class="card admin-card border-0 shadow-sm p-4 h-100">
            <div class="small text-uppercase text-white-50 mb-2">Assigned Incidents</div>
            <div class="display-6 fw-bold text-white">{{ $incidents->count() }}</div>
            <p class="text-muted mt-2">Incidents currently assigned to your fleet.</p>
        </div>
    </div>
</div>

<div class="card admin-card border-0 shadow-sm p-4">
    <h2 class="h5 text-white mb-3">Assigned Incident List</h2>
    @if($incidents->count())
    <div class="list-group list-group-flush">
        @foreach($incidents as $incident)
        <div class="list-group-item bg-transparent border-0 px-0 py-3">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <div class="fw-semibold text-white">{{ $incident->incident_number }} — {{ $incident->location }}</div>
                    <div class="small text-white-50">Status: {{ ucfirst($incident->status) }}</div>
                </div>
                <span class="badge bg-secondary text-uppercase">{{ $incident->status }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center text-muted py-4">No assigned incidents at this time.</div>
    @endif
</div>
@endsection