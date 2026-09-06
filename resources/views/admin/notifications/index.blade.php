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

    @if($notifications->hasPages())
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
<style>
    .admin-notifications-page {
        max-width: 1120px;
    }

    .notifications-eyebrow {
        color: #66d9ef;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .14em;
        margin-bottom: .35rem;
    }

    .notifications-list {
        display: grid;
        gap: .75rem;
    }

    .notification-card {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: center;
        padding: .85rem 1rem;
        border: 1px solid rgba(102, 217, 239, .16);
        border-left: 3px solid transparent;
        border-radius: .85rem;
        background: rgba(16, 43, 69, .86);
        box-shadow: 0 8px 22px rgba(2, 18, 32, .16);
        transition: border-color .18s ease, transform .18s ease, background .18s ease;
    }

    .notification-card:hover {
        border-color: rgba(102, 217, 239, .46);
        transform: translateY(-1px);
    }

    .notification-card-unread {
        border-left-color: #ff6b4a;
        background: rgba(20, 54, 82, .96);
    }

    .notification-open-form {
        min-width: 0;
    }

    .notification-open {
        display: grid;
        grid-template-columns: 2.6rem minmax(0, 1fr);
        gap: .85rem;
        width: 100%;
        padding: 0;
        border: 0;
        background: transparent;
        color: #f4f8fb;
    }

    .notification-open:hover .notification-title {
        color: #7de5f4;
    }

    .notification-icon {
        display: grid;
        place-items: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: .7rem;
        background: rgba(102, 217, 239, .14);
        color: #66d9ef;
        font-size: 1.1rem;
    }

    .notification-card-unread .notification-icon {
        background: rgba(255, 107, 74, .15);
        color: #ff9a78;
    }

    .notification-copy,
    .notification-title,
    .notification-message,
    .notification-date {
        display: block;
    }

    .notification-title {
        margin-bottom: .2rem;
        color: #fff;
        font-size: .98rem;
        font-weight: 700;
        transition: color .18s ease;
    }

    .notification-card-read .notification-title {
        color: #d5e0e8;
    }

    .notification-message {
        color: #c0d0dc;
        font-size: .9rem;
        line-height: 1.45;
    }

    .notification-date {
        margin-top: .45rem;
        color: #86a2b6;
        font-size: .78rem;
    }

    .notification-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: .65rem;
    }

    .notification-status {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .28rem .55rem;
        border-radius: 999px;
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
        background: rgba(25, 135, 84, .18);
        color: #6fe0a7;
    }

    .notification-status-unread {
        background: rgba(255, 107, 74, .16);
        color: #ff9a78;
    }

    .notification-read-form .btn {
        white-space: nowrap;
    }

    .notifications-empty {
        display: grid;
        justify-items: center;
        gap: .35rem;
        padding: 3rem 1rem;
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
        .notifications-header {
            align-items: flex-start !important;
            flex-direction: column;
        }

        .notifications-read-all {
            width: 100%;
        }

        .notification-card {
            grid-template-columns: 1fr;
            gap: .8rem;
        }

        .notification-meta {
            align-items: flex-start;
            flex-direction: row;
            justify-content: space-between;
        }
    }
</style>


@endsection