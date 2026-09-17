@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
        <div>
            <h2 class="section-heading mb-1">Notification Details</h2>
            <p class="section-excerpt mb-0">Review the selected alert and related record.</p>
        </div>
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-light">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Notifications
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="mb-4">
                <div class="text-uppercase small fw-bold text-secondary mb-2">Title</div>
                <h3 class="mb-0">{{ $notification->title }}</h3>
            </div>

            <div class="mb-4">
                <div class="text-uppercase small fw-bold text-secondary mb-2">Message</div>
                <p class="mb-0 fs-6">{{ $notification->message }}</p>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-uppercase small fw-bold text-secondary mb-2">Date</div>
                    <div>{{ $notification->created_at?->format('M d, Y h:i A') }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-uppercase small fw-bold text-secondary mb-2">Status</div>
                    <span class="badge {{ $notification->is_read ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ $notification->is_read ? 'Read' : 'Unread' }}
                    </span>
                </div>
                <div class="col-md-4">
                    <div class="text-uppercase small fw-bold text-secondary mb-2">Type</div>
                    <div>{{ ucfirst($notification->type ?? 'General') }}</div>
                </div>
            </div>

            @php
            $relatedRecord = $notification->relatedRecord();
            $incidentRoute = null;

            if ($relatedRecord instanceof \App\Models\Incident) {
            $incidentRoute = route('admin.incidents.show', $relatedRecord);
            }
            @endphp

            @if($incidentRoute)
            <div class="mt-4 pt-3 border-top">
                <a href="{{ $incidentRoute }}" class="btn btn-primary">
                    <i class="bi bi-link-45deg me-1"></i>
                    View Incident
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection