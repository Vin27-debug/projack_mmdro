@extends('layouts.superadmin')

@section('content')

<div class="container-fluid">

    ```
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold">
                System Settings
            </h2>

            <p class="text-muted">
                Manage MuniResQ system configuration.
            </p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="card shadow-sm">

        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">
                General Settings
            </h5>
        </div>

        <div class="card-body">

            <form method="POST"
                action="{{ route('superadmin.settings.update') }}">

                @csrf

                <div class="mb-3">
                    <label class="form-label">
                        System Name
                    </label>

                    <input
                        type="text"
                        name="system_name"
                        class="form-control"
                        value="MuniResQ">
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Municipality Name
                    </label>

                    <input
                        type="text"
                        name="municipality_name"
                        class="form-control"
                        value="MDRRMO">
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Contact Number
                    </label>

                    <input
                        type="text"
                        name="contact_number"
                        class="form-control"
                        value="">
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="">
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Emergency Hotline
                    </label>

                    <input
                        type="text"
                        name="hotline"
                        class="form-control"
                        value="911">
                </div>

                <div class="form-check form-switch mb-4">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="maintenance_mode"
                        id="maintenance_mode">

                    <label
                        class="form-check-label"
                        for="maintenance_mode">

                        Enable Maintenance Mode

                    </label>

                </div>

                <a href="{{ route('backups.index') }}"
                    class="nav-link">
                    Backup & Restore
                </a>

                <button
                    type="submit"
                    class="btn btn-danger">

                    Save Settings

                </button>

            </form>

        </div>

    </div>
    ```

</div>

@endsection