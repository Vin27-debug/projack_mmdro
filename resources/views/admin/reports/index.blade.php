@extends('layouts.admin')

@section('content')
<div class="reports-page">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div>
            <h1 class="h2 mb-1">Incident Reports</h1>
            <p class="text-white-50 mb-0">Review submitted incident reports and approve them for closure.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="card border rounded-4 shadow-sm bg-dark text-light">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3 align-items-end mb-4">
                <div class="col-12 col-md-6">
                    <label for="report-search" class="form-label">Search reports</label>
                    <input id="report-search" name="search" type="search" value="{{ $search }}" class="form-control bg-dark text-light border-secondary" placeholder="Incident number, driver, or report details">
                </div>
                <div class="col-12 col-md-3">
                    <label for="report-status" class="form-label">Status</label>
                    <select id="report-status" name="status" class="form-select bg-dark text-light border-secondary">
                        <option value="">All statuses</option>
                        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'available' => 'Available (legacy)'] as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Apply filters</button>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-light">Clear</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Incident Number</th>
                            <th scope="col">Driver</th>
                            <th scope="col">Report Details</th>
                            <th scope="col">Status</th>
                            <th scope="col">Submitted</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>#{{ $report->id }}</td>
                                <td>{{ $report->incident?->incident_number ?? 'N/A' }}</td>
                                <td>{{ $report->driver?->user?->name ?? 'N/A' }}</td>
                                <td>
                                    <details>
                                        <summary>{{ \Illuminate\Support\Str::limit($report->summary, 100) }}</summary>
                                        <div class="small mt-2">
                                            <div><strong>Actions taken:</strong> {{ $report->actions_taken }}</div>
                                            @if($report->casualties)
                                                <div><strong>Casualties:</strong> {{ $report->casualties }}</div>
                                            @endif
                                            @if($report->remarks)
                                                <div><strong>Remarks:</strong> {{ $report->remarks }}</div>
                                            @endif
                                        </div>
                                    </details>
                                </td>
                                <td>
                                    @if($report->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($report->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($report->status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($report->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $report->submitted_at?->format('M d, Y h:i A') ?? '—' }}</td>
                                <td>
                                    @if($report->status === 'pending')
                                        <form method="POST" action="{{ route('admin.reports.approve', $report) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                    @else
                                        <span class="text-white-50">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-white-50 py-4">No reports found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-3">
                <span class="small text-white-50">Showing {{ $reports->firstItem() ?? 0 }}–{{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }} reports</span>
                {{ $reports->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <style>
        .reports-page .pagination {
            --bs-pagination-bg: #0b2043;
            --bs-pagination-color: #eef4ff;
            --bs-pagination-border-color: rgba(255, 255, 255, 0.2);
            --bs-pagination-hover-bg: #123263;
            --bs-pagination-hover-color: #fff;
            --bs-pagination-hover-border-color: rgba(255, 255, 255, 0.35);
            --bs-pagination-disabled-bg: #071a38;
            --bs-pagination-disabled-color: #94a3b8;
        }
    </style>
</div>
@endsection