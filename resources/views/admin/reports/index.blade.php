@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <div>
        <h2 class="section-heading mb-1 text-danger">Reports Center</h2>
        <p class="section-excerpt mb-0">Review submitted incident reports and approve them for closure.</p>
    </div>
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
                        <th>ID</th>
                        <th>Incident Number</th>
                        <th>Driver</th>
                        <th>Summary</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                    <tr>
                        <td>#{{ $report->id }}</td>
                        <td>{{ $report->incident?->incident_number ?? 'N/A' }}</td>
                        <td>{{ $report->driver?->user?->name ?? 'N/A' }}</td>
                        <td>{{ $report->summary }}</td>
                        <td>
                            @if($report->status == 'pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($report->status == 'approved')
                            <span class="badge bg-success">Approved</span>
                            @elseif($report->status == 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                            @else
                            <span class="badge bg-secondary">{{ $report->status }}</span>
                            @endif
                        </td>
                        <td>{{ $report->submitted_at }}</td>
                        <td>
                            @if($report->status == 'pending')
                            <form method="POST" action="{{ route('admin.reports.approve', $report) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No reports found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection