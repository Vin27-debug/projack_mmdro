@extends('layouts.admin')

@section('content')

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">

    <div>
        <h2 class="section-heading mb-1">
            Incident {{ $incident->incident_number }}
        </h2>

        <p class="section-excerpt mb-0">
            Official incident record, response assignment, dispatch history, and attachments.
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">

        <button
            type="button"
            onclick="window.print()"
            class="btn btn-outline-dark">
            <i class="bi bi-printer"></i>
            Print Record
        </button>

        @if(!$incident->archived_at)

        <a
            href="{{ route('admin.incidents.edit', $incident) }}"
            class="btn btn-outline-primary">
            <i class="bi bi-pencil"></i>
            Edit
        </a>

        <form
            method="POST"
            action="{{ route('admin.incidents.archive', $incident) }}"
            onsubmit="return confirm('Archive this official incident record? It will remain searchable and will not be deleted.');">
            @csrf

            <button class="btn btn-dark">
                <i class="bi bi-archive"></i>
                Archive
            </button>
        </form>

        @else

        <span class="badge bg-dark d-flex align-items-center px-3">
            ARCHIVED
        </span>

        <form
            method="POST"
            action="{{ route('admin.incidents.restore', $incident) }}">
            @csrf

            <button class="btn btn-outline-success">
                <i class="bi bi-arrow-counterclockwise"></i>
                Restore
            </button>
        </form>

        @endif

    </div>
</div>


{{-- ALERTS --}}

@if(session('success'))

<div class="alert alert-success">
    <i class="bi bi-check-circle me-2"></i>
    {{ session('success') }}
</div>

@endif

@if(session('error'))

<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle me-2"></i>
    {{ session('error') }}
</div>

@endif


<div class="row g-4">

    {{-- ========================================================= --}}
    {{-- INCIDENT DETAILS --}}
    {{-- ========================================================= --}}

    <div class="col-lg-8">

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-dark text-white">
                <i class="bi bi-file-earmark-text me-2"></i>
                Incident Details
            </div>

            <div class="card-body">

                <div class="row g-4">

                    <div class="col-md-6">

                        <div class="incident-field">
                            <div class="field-label">Reporter</div>

                            <div class="field-value">
                                {{ $incident->reporter_name ?: 'N/A' }}
                            </div>
                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="incident-field">
                            <div class="field-label">Contact</div>

                            <div class="field-value">
                                {{ $incident->contact_number ?: 'N/A' }}
                            </div>
                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="incident-field">
                            <div class="field-label">Incident Type</div>

                            <div class="field-value">
                                {{ $incident->incident_type ?: 'N/A' }}
                            </div>
                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="incident-field">
                            <div class="field-label">Priority</div>

                            <div class="field-value">
                                {{ $incident->priority ?: 'N/A' }}
                            </div>
                        </div>

                    </div>


                    <div class="col-12">

                        <div class="incident-field">

                            <div class="field-label">
                                <i class="bi bi-geo-alt me-1"></i>
                                Location
                            </div>

                            <div class="field-value">
                                {{ $incident->location ?: 'N/A' }}
                            </div>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="incident-field">

                            <div class="field-label">
                                Coordinates
                            </div>

                            <div class="field-value">
                                {{ $incident->latitude ?? 'N/A' }},
                                {{ $incident->longitude ?? 'N/A' }}
                            </div>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="incident-field">

                            <div class="field-label">
                                Status
                            </div>

                            <div class="field-value">

                                @php
                                $status = strtolower($incident->status ?? 'unknown');

                                $statusClass = match($status) {
                                'pending' => 'bg-warning text-dark',
                                'dispatched' => 'bg-primary',
                                'responding' => 'bg-info text-dark',
                                'completed' => 'bg-success',
                                'closed' => 'bg-dark',
                                'cancelled' => 'bg-danger',
                                default => 'bg-secondary',
                                };
                                @endphp

                                <span class="badge {{ $statusClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </span>

                            </div>

                        </div>

                    </div>


                    <div class="col-12">

                        <div class="incident-field">

                            <div class="field-label">
                                Description
                            </div>

                            <div class="field-value">

                                {!! nl2br(
                                e(
                                $incident->description
                                ?: 'No description provided.'
                                )
                                ) !!}

                            </div>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="incident-field">

                            <div class="field-label">
                                Created
                            </div>

                            <div class="field-value">
                                {{ $incident->created_at?->format('M d, Y h:i A') }}
                            </div>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="incident-field">

                            <div class="field-label">
                                Archived
                            </div>

                            <div class="field-value">
                                {{ $incident->archived_at?->format('M d, Y h:i A') ?? 'Not archived' }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- ATTACHMENTS --}}

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-primary text-white d-flex justify-content-between">

                <span>
                    <i class="bi bi-paperclip me-2"></i>
                    Photos & Documents
                </span>

                <span>
                    {{ $incident->attachments->count() }}
                </span>

            </div>


            <div class="card-body">

                @forelse($incident->attachments as $attachment)

                <div class="d-flex justify-content-between align-items-center border-bottom py-3 gap-3">

                    <div>

                        <div class="fw-semibold">
                            {{ $attachment->original_name }}
                        </div>

                        <div class="small text-muted">
                            {{ $attachment->mime_type ?: 'File' }}
                            ·
                            {{ number_format($attachment->size / 1024, 1) }} KB
                            ·
                            {{ $attachment->created_at?->format('M d, Y h:i A') }}
                        </div>

                    </div>


                    <a
                        href="{{ route(
                                'admin.incidents.attachments.download',
                                [$incident, $attachment]
                            ) }}"
                        class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-download"></i>
                        Download
                    </a>

                </div>

                @empty

                <div class="text-muted">
                    No photos or documents attached to this incident.
                </div>

                @endforelse

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- RIGHT SIDE --}}
    {{-- ========================================================= --}}

    <div class="col-lg-4">


        {{-- RESPONSE ASSIGNMENT --}}

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-dark text-white">

                <i class="bi bi-ambulance me-2"></i>
                Response Assignment

            </div>


            <div class="card-body">


                {{-- DRIVER --}}

                <div class="assignment-row mb-3">

                    <div class="field-label">
                        Driver
                    </div>

                    <div class="field-value">

                        @if($incident->driver?->user)

                        <i class="bi bi-person-check text-success me-1"></i>

                        {{ $incident->driver->user->name }}

                        @else

                        <span class="text-muted">
                            Unassigned
                        </span>

                        @endif

                    </div>

                </div>


                {{-- AMBULANCE --}}

                <div class="assignment-row mb-3">

                    <div class="field-label">
                        Ambulance
                    </div>

                    <div class="field-value">

                        @if($incident->ambulance)

                        <i class="bi bi-truck text-success me-1"></i>

                        {{ $incident->ambulance->plate_number }}

                        @else

                        <span class="text-muted">
                            Unassigned
                        </span>

                        @endif

                    </div>

                </div>


                {{-- DISPATCH BUTTON --}}

                @if(
                !$incident->archived_at &&
                in_array($incident->status, [
                \App\Models\Incident::STATUS_PENDING
                ], true)
                )

                <div class="border-top pt-3 mt-3">

                    <a
                        href="{{ route(
                                'admin.incidents.dispatch.form',
                                $incident
                            ) }}"
                        class="btn btn-primary w-100">

                        <i class="bi bi-send me-1"></i>

                        Dispatch Response

                    </a>

                </div>

                @elseif(
                in_array($incident->status, [
                \App\Models\Incident::STATUS_DISPATCHED,
                \App\Models\Incident::STATUS_RESPONDING
                ], true)
                )

                <div class="border-top pt-3 mt-3">

                    <div class="alert alert-primary mb-0">

                        <i class="bi bi-broadcast me-1"></i>

                        Response currently active.

                    </div>

                </div>

                @elseif(
                $incident->status === \App\Models\Incident::STATUS_COMPLETED
                )

                <div class="border-top pt-3 mt-3">

                    <div class="alert alert-success mb-0">

                        <i class="bi bi-check-circle me-1"></i>

                        Response completed.

                    </div>

                </div>

                @endif

            </div>

        </div>


        {{-- DISPATCH HISTORY --}}

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-dark text-white">

                <i class="bi bi-clock-history me-2"></i>
                Dispatch History

            </div>


            <div class="card-body">

                @forelse($incident->dispatches as $dispatch)

                <div class="dispatch-history-item">

                    <div class="d-flex justify-content-between align-items-center">

                        <strong>
                            {{ ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $dispatch->status
                                    )
                                ) }}
                        </strong>

                        <span class="small text-muted">
                            {{ $dispatch->created_at?->format('M d, Y h:i A') }}
                        </span>

                    </div>


                    <div class="small text-muted mt-1">

                        <i class="bi bi-person me-1"></i>

                        {{ $dispatch->driver?->user?->name ?? 'Driver' }}

                        <span class="mx-1">·</span>

                        <i class="bi bi-truck me-1"></i>

                        {{ $dispatch->vehicle?->plate_number ?? 'Ambulance' }}

                    </div>

                </div>

                @empty

                <div class="text-muted">
                    No dispatch records.
                </div>

                @endforelse

            </div>

        </div>

    </div>

</div>


<style>
    .incident-field {
        padding-bottom: 4px;
    }

    .field-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6c757d;
        margin-bottom: 4px;
    }

    .field-value {
        color: #212529;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .assignment-row {
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }

    .dispatch-history-item {
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .dispatch-history-item:last-child {
        border-bottom: 0;
    }
</style>


<style media="print">
    .admin-sidebar,
    nav,
    .btn,
    form,
    .section-heading+p {
        display: none !important;
    }

    .admin-shell,
    .container-fluid {
        width: 100% !important;
        max-width: none !important;
    }

    body {
        background: #fff !important;
        color: #000 !important;
    }
</style>

@endsection