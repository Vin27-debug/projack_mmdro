@extends('layouts.admin')

@section('content')

<div class="container-fluid admin-notifications-page">

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="notifications-header d-flex justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="section-heading mb-1">Notifications</h2>
            <p class="section-excerpt mb-0">{{ $unreadNotifications ?? 0 }} unread messages</p>
        </div>

        <form method="POST" action="{{ route('admin.notifications.read-all') }}" class="mb-0 flex-shrink-0">
            @csrf
            <button class="btn btn-success notifications-read-all">
                <i class="bi bi-check2-all me-1"></i>
                Mark All Read
            </button>
        </form>
    </div>

    <div class="notifications-panel">
        <div class="notifications-table-head">
            <div>Type / Title</div>
            <div>Message</div>
            <div>Date</div>
            <div>Status</div>
            <div>Action</div>
        </div>

        <div class="notifications-list">
            @forelse($notifications as $notification)
            <div class="notification-row {{ $notification->is_read ? 'notification-row-read' : 'notification-row-unread' }}">
                <form method="POST" action="{{ route('admin.notifications.open', $notification) }}" class="notification-open-form">
                    @csrf
                    <button type="submit" class="notification-open text-start">
                        <span class="notification-icon" aria-hidden="true">
                            @switch($notification->type)
                            @case('panic')
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            @break
                            @case('hijack')
                            <i class="bi bi-shield-exclamation"></i>
                            @break
                            @case('incident')
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            @break
                            @case('maintenance')
                            <i class="bi bi-tools"></i>
                            @break
                            @case('vehicle')
                            @case('dispatch')
                            <i class="bi bi-truck-front-fill"></i>
                            @break
                            @case('report')
                            <i class="bi bi-file-earmark-text"></i>
                            @break
                            @default
                            <i class="bi bi-bell-fill"></i>
                            @endswitch
                        </span>

                        <span class="notification-copy">
                            <span class="notification-title">{{ $notification->title }}</span>
                        </span>
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.notifications.open', $notification) }}" class="notification-message-form">
                    @csrf
                    <button type="submit" class="notification-cell-link text-start">
                        {{ $notification->message }}
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.notifications.open', $notification) }}" class="notification-date-form">
                    @csrf
                    <button type="submit" class="notification-cell-link text-start">
                        {{ $notification->created_at?->format('M j, Y') }}<br>
                        <span>{{ $notification->created_at?->format('h:i A') }}</span>
                    </button>
                </form>

                <div class="notification-meta">
                    <span class="notification-status {{ $notification->is_read ? 'notification-status-read' : 'notification-status-unread' }}">
                        <span class="notification-status-dot"></span>
                        {{ $notification->is_read ? 'Read' : 'Unread' }}
                    </span>
                </div>

                <div class="notification-action">
                    @if(!$notification->is_read)
                    <form method="POST" action="{{ route('admin.notifications.read', $notification) }}" class="notification-read-form">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">Mark Read</button>
                    </form>
                    @else
                    <span class="notification-dash">—</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="notifications-empty">
                <i class="bi bi-bell-slash"></i>
                <strong>No notifications yet</strong>
                <span>New command alerts will appear here.</span>
            </div>
            @endforelse
        </div>
    </div>

    @if($notifications->hasPages())
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
<style>
    .admin-notifications-page {
        max-width: 1080px;
    }

    .notifications-list {
        border-top: 1px solid #263e59;
        border-bottom: 1px solid #263e59;
    }

    .notifications-panel {
        background: #0d1d33;
        border: 1px solid #263e59;
        border-radius: 4px;
    }

    .notifications-table-head,
    .notification-row {
        display: grid;
        grid-template-columns: minmax(180px, 1.25fr) minmax(240px, 2fr) 120px 90px 108px;
        gap: .75rem;
        align-items: center;
    }

    .notifications-table-head {
        padding: .55rem .8rem;
        border-bottom: 1px solid #263e59;
        color: #aab8c8;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .notification-row {
        min-height: 76px;
        padding: .65rem .8rem;
        border-bottom: 1px solid #263e59;
        border-left: 3px solid transparent;
        background: #0d1d33;
    }

    .notification-row:last-child {
        border-bottom: 0;
    }

    .notification-row:hover {
        border-left-color: #014cfd;
        background: #10243d;
    }

    .notification-row-unread {
        border-left-color: #ff6b4a;
        background: #10243d;
    }

    .notification-open-form {
        min-width: 0;
    }

    .notification-open {
        display: grid;
        grid-template-columns: 1.75rem minmax(0, 1fr);
        gap: .7rem;
        width: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        color: #f4f8fb;
    }

    .notification-open:hover .notification-title,
    .notification-cell-link:hover {
        color: #014cfd;
    }

    .notification-icon {
        display: grid;
        place-items: center;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 3px;
        background: transparent;
        color: #014cfd;
        font-size: 1rem;
    }

    .notification-row-unread .notification-icon {
        color: #ff8a78;
    }

    .notification-copy,
    .notification-title,
    .notification-message,
    .notification-date {
        display: block;
    }

    .notification-title {
        margin-bottom: .1rem;
        color: #fff;
        font-size: .9rem;
        font-weight: 700;
        transition: color .18s ease;
    }

    .notification-row-read .notification-title {
        color: #d5e0e8;
    }

    .notification-message-form,
    .notification-date-form {
        min-width: 0;
    }

    .notification-cell-link {
        display: block;
        width: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        color: #c4d0dc;
        font-size: .8rem;
        line-height: 1.35;
    }

    .notification-cell-link span,
    .notification-date-form .notification-cell-link {
        color: #8698ab;
        font-size: .72rem;
    }

    .notification-meta {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
    }

    .notification-status {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .notification-status-dot {
        width: .45rem;
        height: .45rem;
        border-radius: 50%;
        background: currentColor;
    }

    .notification-status-read {
        background: transparent;
        color: #75c795;
    }

    .notification-status-unread {
        background: transparent;
        color: #ff8a78;
    }

    .notification-read-form .btn {
        border-color: #014cfd;
        color: #014cfd;
        white-space: nowrap;
    }

    .notification-read-form .btn:hover,
    .notification-read-form .btn:focus-visible {
        background: #014cfd;
        border-color: #003399;
        color: #fff;
    }

    .notifications-empty {
        display: grid;
        justify-items: center;
        gap: .35rem;
        padding: 2rem 1rem;
        border: 1px solid #263e59;
        border-radius: 4px;
        color: #a7bdcb;
        text-align: center;
    }

    .notifications-empty i {
        margin-bottom: .35rem;
        color: #014cfd;
        font-size: 1.8rem;
    }

    @media (max-width: 767.98px) {
        .notifications-table-head {
            display: none;
        }

        .notification-row {
            grid-template-columns: 1fr;
            gap: .55rem;
            padding-left: .65rem;
        }

        .notification-open,
        .notification-message-form,
        .notification-date-form,
        .notification-meta,
        .notification-action {
            width: 100%;
        }

        .notification-meta {
            align-items: flex-start;
            flex-direction: row;
            justify-content: space-between;
        }

        .notification-action {
            text-align: left;
        }
    }
</style>


@endsection