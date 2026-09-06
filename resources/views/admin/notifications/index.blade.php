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

    <div class="notifications-panel table-responsive">
        <table class="table notifications-table mb-0">
            <thead>
                <tr>
                    <th scope="col">Type / Title</th>
                    <th scope="col">Message</th>
                    <th scope="col">Date</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                <tr class="notification-row {{ $notification->is_read ? 'notification-row-read' : 'notification-row-unread' }}">
                    <td>
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
                    </td>

                    <td>
                        <form method="POST" action="{{ route('admin.notifications.open', $notification) }}" class="notification-message-form">
                            @csrf
                            <button type="submit" class="notification-cell-link text-start">
                                {{ $notification->message }}
                            </button>
                        </form>
                    </td>

                    <td>
                        <form method="POST" action="{{ route('admin.notifications.open', $notification) }}" class="notification-date-form">
                            @csrf
                            <button type="submit" class="notification-cell-link text-start">
                                {{ $notification->created_at?->format('M j, Y') }}<br>
                                <span>{{ $notification->created_at?->format('h:i A') }}</span>
                            </button>
                        </form>
                    </td>

                    <td>
                        <div class="notification-meta">
                            <span class="notification-status {{ $notification->is_read ? 'notification-status-read' : 'notification-status-unread' }}">
                                <span class="notification-status-dot"></span>
                                {{ $notification->is_read ? 'Read' : 'Unread' }}
                            </span>
                        </div>
                    </td>

                    <td>
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
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="notifications-empty">No notifications yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
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

    .notifications-panel {
        background: #0d1d33;
        border: 1px solid #263e59;
        border-radius: 4px;
    }

    .notifications-table {
        --bs-table-bg: transparent;
        --bs-table-color: #f4f7fb;
        --bs-table-border-color: #263e59;
        table-layout: fixed;
        min-width: 760px;
    }

    .notifications-table thead th {
        padding: .6rem .75rem;
        color: #aab8c8;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .notifications-table th:nth-child(1) {
        width: 25%;
    }

    .notifications-table th:nth-child(2) {
        width: 37%;
    }

    .notifications-table th:nth-child(3) {
        width: 15%;
    }

    .notifications-table th:nth-child(4) {
        width: 11%;
    }

    .notifications-table th:nth-child(5) {
        width: 12%;
    }

    .notifications-table tbody tr {
        border-left: 3px solid transparent;
        background: #0d1d33;
    }

    .notifications-table tbody td {
        padding: .65rem .75rem;
        vertical-align: middle;
        border-bottom: 1px solid #263e59;
    }

    .notifications-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .notifications-table tbody tr:hover {
        border-left-color: #014cfd;
        background: #10243d;
    }

    .notifications-table tbody tr.notification-row-unread {
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
        border: 1px solid #263e59;
        color: #a7bdcb;
        padding: 1.25rem;
        text-align: center;
    }

    .notifications-empty i {
        margin-bottom: .35rem;
        color: #014cfd;
        font-size: 1.8rem;
    }

    @media (max-width: 767.98px) {
        .notifications-table {
            table-layout: auto;
            min-width: 680px;
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