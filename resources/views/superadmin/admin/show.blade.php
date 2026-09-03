@extends('layouts.superadmin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-uppercase text-muted small fw-semibold">Administrator account</div>
            <h1 class="h2 mb-1">{{ $admin->name }}</h1>
            <p class="text-muted mb-0">{{ $admin->email }}</p>
        </div>
        <a href="{{ route('admins.index') }}" class="btn btn-outline-secondary">Back to Admin Management</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-4">
                @foreach([
                'Full Name' => $admin->name,
                'Employee ID' => $admin->employee_id ?: 'N/A',
                'Position' => $admin->position ?: 'N/A',
                'Department' => $admin->department ?: 'N/A',
                'Office Assignment' => $admin->office ?: 'N/A',
                'Government Email' => $admin->email,
                'Contact Number' => $admin->contact_number ?: 'N/A',
                'Created At' => $admin->created_at?->format('F j, Y h:i A'),
                'Approved At' => $admin->status === 'pending' ? 'Not yet approved' : ($admin->approved_at?->format('F j, Y h:i A') ?: 'N/A'),
                'Approved By' => $admin->approvedBy?->name ?: 'N/A',
                ] as $label => $value)
                <div class="col-md-6">
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="fw-semibold">{{ $value }}</div>
                </div>
                @endforeach
                <div class="col-md-6">
                    <div class="text-muted small">Status</div><span class="badge bg-{{ ['pending' => 'warning text-dark', 'approved' => 'success', 'rejected' => 'danger', 'suspended' => 'dark'][$admin->status] ?? 'secondary' }}">{{ ucfirst($admin->status) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection