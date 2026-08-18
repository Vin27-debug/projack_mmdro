@extends('layouts.superadmin')

@section('content')

<div class="page-header mb-4">
    <div>
        <div class="small text-uppercase text-white-50 mb-2">
            Driver Management
        </div>

        <h1 class="page-title">
            Create Driver Account
        </h1>

        <p class="page-subtitle mb-0">
            Create an approved driver account for MuniResQ.
        </p>
    </div>
</div>

<div class="card admin-card border-0 shadow-sm p-4">

    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Please check the following:</strong>

        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('superadmin.drivers.store') }}">
        @csrf

        <div class="row g-3">

            {{-- NAME --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Driver Name
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="form-control"
                    placeholder="Juan Dela Cruz"
                    required>
            </div>

            {{-- EMAIL --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control"
                    placeholder="driver@muniresq.com"
                    required>
            </div>

            {{-- PASSWORD --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Minimum 8 characters"
                    required>
            </div>

            {{-- CONFIRM PASSWORD --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    Confirm Password
                </label>

                <input
                    type="password"
                    name="password_confirmation"
                    class="form-control"
                    placeholder="Repeat password"
                    required>
            </div>

            <div class="col-12">
                <hr>
            </div>

            {{-- CONTACT --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    Contact Number
                </label>

                <input
                    type="text"
                    name="contact_number"
                    value="{{ old('contact_number') }}"
                    class="form-control"
                    placeholder="09XXXXXXXXX">
            </div>

            {{-- LICENSE --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    License Number
                </label>

                <input
                    type="text"
                    name="license_number"
                    value="{{ old('license_number') }}"
                    class="form-control"
                    placeholder="License number">
            </div>

            {{-- LICENSE EXPIRY --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold">
                    License Expiry
                </label>

                <input
                    type="date"
                    name="license_expiry"
                    value="{{ old('license_expiry') }}"
                    class="form-control">
            </div>

        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">

            <a
                href="{{ route('superadmin.drivers') }}"
                class="btn btn-secondary">
                Cancel
            </a>

            <button
                type="submit"
                class="btn btn-primary">
                Create Driver
            </button>

        </div>

    </form>

</div>

@endsection