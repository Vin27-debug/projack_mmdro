@extends('layouts.superadmin')

@section('content')
<div class="page-header">
    <div>
        <div class="small text-uppercase text-white-50 mb-2">Driver Reports</div>
        <h1 class="page-title">Driver Performance</h1>
        <p class="page-subtitle mb-0">A quick overview of dispatch and incident report counts per driver.</p>
    </div>
</div>

<div class="card admin-card border-0 shadow-sm p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Driver</th>
                    <th>Total Dispatches</th>
                    <th>Total Reports</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $driver)
                <tr>
                    <td>{{ $driver->user->name ?? 'N/A' }}</td>
                    <td>{{ $driver->dispatches_count }}</td>
                    <td>{{ $driver->incident_reports_count }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">No driver performance data available.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection