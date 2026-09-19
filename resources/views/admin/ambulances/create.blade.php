 @extends('layouts.admin')

 @section('content')

 <div class="container-fluid">

     {{-- HEADER --}}
     <div class="d-flex justify-content-between align-items-center mb-4">
         <div>
             <h2 class="fw-bold mb-1">Add Vehicle</h2>
             <p class="text-muted mb-0">
                 Register a new ambulance, rescue vehicle, fire truck, or police unit.
             </p>
         </div>

         <a href="{{ route('admin.ambulances.index') }}"
             class="btn btn-outline-secondary rounded-3 px-4">
             <i class="bi bi-arrow-left me-1"></i>
             Back to Vehicles
         </a>
     </div>

     {{-- VALIDATION ERRORS --}}
     @if ($errors->any())
     <div class="alert alert-danger border-0 shadow-sm rounded-3">
         <div class="fw-semibold mb-2">
             <i class="bi bi-exclamation-triangle me-2"></i>
             Please fix the following:
         </div>

         <ul class="mb-0">
             @foreach ($errors->all() as $error)
             <li>{{ $error }}</li>
             @endforeach
         </ul>
     </div>
     @endif

     {{-- FORM --}}
     <div class="card border-0 shadow-sm rounded-4">

         <div class="card-header bg-white border-0 p-4">
             <h5 class="fw-bold mb-1">
                 Vehicle Information
             </h5>

             <small class="text-muted">
                 Enter the basic information for the vehicle.
             </small>
         </div>

         <div class="card-body p-4">

             <form method="POST" action="{{ route('admin.ambulances.store') }}">
                 @csrf

                 {{-- PLATE NUMBER --}}
                 <div class="mb-4">
                     <label for="plate_number" class="form-label fw-semibold">
                         Plate Number
                     </label>

                     <input
                         type="text"
                         id="plate_number"
                         name="plate_number"
                         class="form-control @error('plate_number') is-invalid @enderror"
                         value="{{ old('plate_number') }}"
                         placeholder="Example: ABC 1234"
                         required>

                     @error('plate_number')
                     <div class="invalid-feedback">
                         {{ $message }}
                     </div>
                     @enderror
                 </div>

                 {{-- VEHICLE NAME --}}
                 <div class="mb-4">
                     <label for="vehicle_name" class="form-label fw-semibold">
                         Vehicle Name
                     </label>

                     <input
                         type="text"
                         id="vehicle_name"
                         name="vehicle_name"
                         class="form-control @error('vehicle_name') is-invalid @enderror"
                         value="{{ old('vehicle_name') }}"
                         placeholder="Example: MuniResQ Ambulance 01"
                         required>

                     @error('vehicle_name')
                     <div class="invalid-feedback">
                         {{ $message }}
                     </div>
                     @enderror
                 </div>

                 {{-- VEHICLE TYPE --}}
                 <div class="mb-4">
                     <label for="vehicle_type" class="form-label fw-semibold">
                         Vehicle Type
                     </label>

                     <select
                         id="vehicle_type"
                         name="vehicle_type"
                         class="form-select @error('vehicle_type') is-invalid @enderror"
                         required>
                         <option value="">Select Vehicle Type</option>

                         <option value="ambulance"
                             {{ old('vehicle_type') === 'ambulance' ? 'selected' : '' }}>
                             Ambulance
                         </option>

                         <option value="rescue_van"
                             {{ old('vehicle_type') === 'rescue_van' ? 'selected' : '' }}>
                             Rescue Van
                         </option>

                         <option value="fire_truck"
                             {{ old('vehicle_type') === 'fire_truck' ? 'selected' : '' }}>
                             Fire Truck
                         </option>

                         <option value="police"
                             {{ old('vehicle_type') === 'police' ? 'selected' : '' }}>
                             Police
                         </option>
                     </select>

                     @error('vehicle_type')
                     <div class="invalid-feedback">
                         {{ $message }}
                     </div>
                     @enderror
                 </div>

                 {{-- STATUS INFO --}}
                 <div class="alert alert-light border rounded-3 mb-4">
                     <i class="bi bi-info-circle me-2"></i>
                     New vehicles will automatically be registered with
                     <strong>Available</strong> status.
                 </div>

                 {{-- ACTIONS --}}
                 <div class="d-flex justify-content-end gap-2">

                     <a href="{{ route('admin.ambulances.index') }}"
                         class="btn btn-outline-secondary rounded-3 px-4">
                         Cancel
                     </a>

                     <button type="submit"
                         class="btn btn-primary rounded-3 px-4">
                         <i class="bi bi-plus-lg me-1"></i>
                         Add Vehicle
                     </button>

                 </div>

             </form>

         </div>
     </div>

 </div>

 @endsection