@extends('layouts.admin')

@section('content')

<div class="container-fluid admin-notifications-page">

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="notifications-header d-flex justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="notifications-eyebrow"><i class="bi bi-bell-fill me-1"></i> ADMIN COMMAND CENTER</div>
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
        <div class="notifications-list">
            @forelse($notifications as $notification)
            <article class="notification-card {{ $notification->is_read ? 'notification-card-read' : 'notification-card-unread' }}">
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
                            <span class="notification-message">{{ $notification->message }}</span>
                            <span class="notification-date">
                                <i class="bi bi-clock me-1"></i>
                                {{ $notification->created_at?->format('F j, Y') }} · {{ $notification->created_at?->format('h:i A') }}
                            </span>
                        </span>
                    </button>
                </form>

                <div class="notification-meta">
                    <span class="notification-status {{ $notification->is_read ? 'notification-status-read' : 'notification-status-unread' }}">
                        <span class="notification-status-dot"></span>
                        {{ $notification->is_read ? 'Read' : 'Unread' }}
                    </span>

                    @if(!$notification->is_read)
                    <form method="POST" action="{{ route('admin.notifications.read', $notification) }}" class="notification-read-form">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">Mark Read</button>
                    </form>
                    @endif
                </div>
            </article>
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

    .notifications-eyebrow {
        color: #66d9ef;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .14em;
        margin-bottom: .35rem;
    }

    .notifications-list {
        border-top: 1px solid #263e59;
        border-bottom: 1px solid #263e59;
    }

    .notification-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: .9rem;
        align-items: center;
        padding: .8rem .9rem .8rem .75rem;
        border-bottom: 1px solid #263e59;
        border-left: 3px solid transparent;
        background: #0d1d33;
        transition: border-color .18s ease, background .18s ease;
    }

    .notification-card:last-child {
        border-bottom: 0;
    }

    .notification-card:hover {
        border-left-color: #8eb9ff;
        background: #10243d;
    }

    .notification-card-unread {
        border-left-color: #ff6b4a;
        background: #10243d;
    }

    .notification-open-form {
        min-width: 0;
    }

    .notification-open {
        display: grid;
        grid-template-columns: 2rem minmax(0, 1fr);
        gap: .7rem;
        width: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        color: #f4f8fb;
    }

    .notification-open:hover .notification-title {
        color: #8eb9ff;
    }

    .notification-icon {
        display: grid;
        place-items: center;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 3px;
        background: transparent;
        color: #8eb9ff;
        font-size: 1rem;
    }

    .notification-card-unread .notification-icon {
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

    .notification-card-read .notification-title {
        color: #d5e0e8;
    }

    .notification-date {
        margin-top: .3rem;
        color: #86a2b6;
        font-size: .72rem;
    }

    .notification-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: .45rem;
    }

    .notification-status {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: 0;
        border-radius: 0;
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
        white-space: nowrap;
    }

    .notifications-empty {
        display: grid;
        justify-items: center;
        gap: .35rem;
        padding: 2rem 1rem;
        border: 1px dashed rgba(102, 217, 239, .24);
        border-radius: .85rem;
        color: #a7bdcb;
        text-align: center;
    }

    .notifications-empty i {
        margin-bottom: .35rem;
        color: #66d9ef;
        font-size: 1.8rem;
    }

    @media (max-width: 767.98px) {
        .notification-card {
            grid-template-columns: 1fr;
            gap: .55rem;
            padding-left: .65rem;
        }

        .notification-meta {
            align-items: flex-start;
            flex-direction: row;
            justify-content: space-between;
        }
    }
</style>


@endsection