<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Vehicle</label>
        <select name="ambulance_id" class="form-select @error('ambulance_id') is-invalid @enderror" required>
            <option value="">Select vehicle</option>
            @foreach($ambulances as $ambulance)
            <option value="{{ $ambulance->id }}" {{ old('ambulance_id', $vehicleMaintenance->ambulance_id ?? '') == $ambulance->id ? 'selected' : '' }}>
                {{ $ambulance->vehicle_name }}
            </option>
            @endforeach
        </select>
        @error('ambulance_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Maintenance Type</label>
        <input type="text" name="maintenance_type" class="form-control @error('maintenance_type') is-invalid @enderror" value="{{ old('maintenance_type', $vehicleMaintenance->maintenance_type ?? '') }}" required>
        @error('maintenance_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Scheduled Date</label>
        <input type="date" name="scheduled_date" class="form-control @error('scheduled_date') is-invalid @enderror" value="{{ old('scheduled_date', optional($vehicleMaintenance->scheduled_date ?? null)->format('Y-m-d') ?? '') }}" required>
        @error('scheduled_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="scheduled" {{ old('status', $vehicleMaintenance->status ?? '') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
            <option value="in_progress" {{ old('status', $vehicleMaintenance->status ?? '') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
            <option value="completed" {{ old('status', $vehicleMaintenance->status ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ old('status', $vehicleMaintenance->status ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
        @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Vehicle Status</label>
        <select name="vehicle_status" class="form-select @error('vehicle_status') is-invalid @enderror">
            <option value="">Keep current status</option>
            @foreach($vehicleStatuses as $status)
            <option value="{{ $status }}" {{ old('vehicle_status', $vehicleMaintenance->ambulance->status ?? '') === $status ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        @error('vehicle_status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $vehicleMaintenance->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.maintenance.index') }}" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary">Save Record</button>
</div>