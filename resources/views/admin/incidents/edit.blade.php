@extends('layouts.admin')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="section-heading mb-1">Edit Incident {{ $incident->incident_number }}</h2>
            <p class="section-excerpt mb-0">Correct official incident details without deleting the historical record.</p>
        </div>
        <a href="{{ route('admin.incidents.show', $incident) }}" class="btn btn-outline-secondary">Back to Record</a>
    </div>

    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('admin.incidents.update', $incident) }}" enctype="multipart/form-data" class="card border-0 shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Reporter Name</label><input name="reporter_name" class="form-control" required value="{{ old('reporter_name', $incident->reporter_name) }}"></div>
                <div class="col-md-6"><label class="form-label">Contact Number</label><input name="contact_number" class="form-control" value="{{ old('contact_number', $incident->contact_number) }}"></div>
                <div class="col-md-6"><label class="form-label">Incident Type</label><input name="incident_type" class="form-control" required value="{{ old('incident_type', $incident->incident_type) }}"></div>
                <div class="col-md-6"><label class="form-label">Priority</label><select name="priority" class="form-select">@foreach(['Low','Medium','High','Critical'] as $priority)<option value="{{ $priority }}" @selected(old('priority', $incident->priority) === $priority)>{{ $priority }}</option>@endforeach</select></div>
                <div class="col-12"><label class="form-label">Location</label><input name="location" class="form-control" value="{{ old('location', $incident->location) }}"></div>
                <div class="col-md-6"><label class="form-label">Latitude</label><input type="number" step="any" name="latitude" class="form-control" value="{{ old('latitude', $incident->latitude) }}"></div>
                <div class="col-md-6"><label class="form-label">Longitude</label><input type="number" step="any" name="longitude" class="form-control" value="{{ old('longitude', $incident->longitude) }}"></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" rows="5" class="form-control">{{ old('description', $incident->description) }}</textarea></div>
                <div class="col-12"><label class="form-label fw-semibold">Add Photos / Documents</label><input type="file" name="attachments[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx"><div class="form-text">Up to 10 MB per file. Existing attachments are retained.</div></div>
            </div>
        </div>
        <div class="card-footer bg-transparent d-flex gap-2">
            <button class="btn btn-primary">Save Changes</button>
            <a href="{{ route('admin.incidents.show', $incident) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
