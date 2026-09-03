@extends('layouts.superadmin')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <div class="text-uppercase text-muted small fw-semibold">Admin Management</div>
        <h1 class="h2 mb-1">Edit Administrator</h1>
        <p class="text-muted mb-0">Update government administrator account information.</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admins.update', $admin) }}" class="card border-0 shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body row g-3">
            @foreach([
            'name' => 'Full Name',
            'employee_id' => 'Employee ID',
            'position' => 'Official Position',
            'department' => 'Department',
            'office' => 'Office Assignment',
            'contact_number' => 'Contact Number',
            'email' => 'Official Government Email',
            ] as $field => $label)
            <div class="col-md-6">
                <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $admin->{$field}) }}" class="form-control" required>
            </div>
            @endforeach
        </div>
        <div class="card-footer bg-transparent d-flex gap-2 justify-content-end">
            <a href="{{ route('admins.show', $admin) }}" class="btn btn-outline-secondary">Cancel</a>
            <button class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection