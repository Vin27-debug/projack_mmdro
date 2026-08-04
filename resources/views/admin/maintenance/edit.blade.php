@extends('layouts.admin')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="section-heading mb-1">Edit Maintenance Record</h2>
            <p class="section-excerpt mb-0">Adjust the maintenance details and vehicle status.</p>
        </div>
        <a href="{{ route('admin.maintenance.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.maintenance.update', $vehicleMaintenance) }}">
                @csrf
                @method('PUT')
                @include('admin.maintenance._form')
            </form>
        </div>
    </div>
</div>
@endsection