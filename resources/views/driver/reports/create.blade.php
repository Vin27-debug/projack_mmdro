@extends('layouts.driver')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">📝 Incident Report</h2>
            <p class="text-muted mb-0">
                Complete the emergency response report.
            </p>
        </div>
    </div>

    <div class="card shadow-sm border-0">

        <div class="card-body p-4">

            @if(!$incident)
            <div class="text-center py-5 text-muted">
                <i class="bi bi-exclamation-circle fs-1 mb-3"></i>
                <h5 class="fw-semibold">No incident available</h5>
                <p class="mb-4">There is no completed incident ready for reporting at the moment.</p>
                <a href="{{ route('driver.dashboard') }}" class="btn btn-primary">Back to Dashboard</a>
            </div>
            @else
            <form method="POST"
                action="{{ route('driver.report.store', $incident) }}">

                @csrf

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Incident Number</label>
                        <input type="text" class="form-control" value="{{ $incident->incident_number }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Incident Type</label>
                        <input type="text" class="form-control" value="{{ $incident->incident_type }}" readonly>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" value="{{ $incident->location }}" readonly>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Summary of Response</label>
                        <textarea name="summary" rows="3" class="form-control">{{ old('summary') }}</textarea>
                        @error('summary')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Actions Taken</label>
                        <textarea name="actions_taken" rows="3" class="form-control">{{ old('actions_taken') }}</textarea>
                        @error('actions_taken')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Casualties</label>
                        <input type="text" name="casualties" value="{{ old('casualties') }}" class="form-control">
                        @error('casualties')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" value="{{ old('remarks') }}" class="form-control">
                        @error('remarks')<div class="text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                </div>

                <hr>

                <div class="text-end">
                    <a href="{{ route('driver.dashboard') }}" class="btn btn-outline-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Report</button>
                </div>

            </form>
            @endif

        </div>

    </div>

</div>

@endsection