@extends('layouts.superadmin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="text-uppercase text-muted small fw-semibold">Super Admin</div>
            <h1 class="h2 mb-1">Admin Management</h1>
            <p class="text-muted mb-0">Manage government administrator accounts</p>
        </div>
        <a href="{{ route('admins.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Create Admin
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" class="card border-0 shadow-sm mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-md-8">
                <label for="search" class="form-label">Search administrators</label>
                <input id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, employee ID, or email">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All</option>
                    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'suspended' => 'Suspended'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-outline-primary" title="Apply filters"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </form>

    @if($admins->isEmpty())
    <div class="card border-0 shadow-sm text-center p-5">
        <i class="bi bi-person-badge fs-1 text-muted"></i>
        <h2 class="h5 mt-3">No administrator accounts found.</h2>
        <a href="{{ route('admins.create') }}" class="btn btn-primary mt-2"><i class="bi bi-plus-lg me-1"></i> Create Admin</a>
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 1050px">
                <thead class="table-light">
                    <tr>
                        <th>Full Name</th>
                        <th>Employee ID</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Office Assignment</th>
                        <th>Government Email</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Approved At</th>
                        <th>Approved By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($admins as $admin)
                    @php
                    $statusClass = ['pending' => 'warning text-dark', 'approved' => 'success', 'rejected' => 'danger', 'suspended' => 'dark'][$admin->status] ?? 'secondary';
                    $createdAt = $admin->created_at ? \Illuminate\Support\Carbon::parse($admin->created_at) : null;
                    $approvedAt = $admin->approved_at ? \Illuminate\Support\Carbon::parse($admin->approved_at) : null;
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $admin->name }}</td>
                        <td>{{ $admin->employee_id ?: 'N/A' }}</td>
                        <td>{{ $admin->position ?: 'N/A' }}</td>
                        <td>{{ $admin->department ?: 'N/A' }}</td>
                        <td>{{ $admin->office ?: 'N/A' }}</td>
                        <td>{{ $admin->email }}</td>
                        <td><span class="badge bg-{{ $statusClass }}">{{ ucfirst($admin->status ?: 'pending') }}</span></td>
                        <td>{{ $createdAt?->format('F j, Y') ?: 'N/A' }}<br><small>{{ $createdAt?->format('h:i A') ?: '' }}</small></td>
                        <td>{{ $admin->status === 'pending' ? 'Not yet approved' : ($approvedAt?->format('F j, Y h:i A') ?: 'N/A') }}</td>
                        <td>{{ $admin->approvedBy?->name ?: 'N/A' }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <a href="{{ route('admins.show', $admin) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                @if($admin->status === 'pending')
                                <form method="POST" action="{{ route('admins.approve', $admin) }}">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                                <form method="POST" action="{{ route('admins.reject', $admin) }}">@csrf<button class="btn btn-sm btn-outline-danger">Reject</button></form>
                                @elseif($admin->status === 'approved')
                                <a href="{{ route('admins.edit', $admin) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('admins.suspend', $admin) }}">@csrf<button class="btn btn-sm btn-dark">Suspend</button></form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection