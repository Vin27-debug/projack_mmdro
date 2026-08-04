@extends('layouts.admin')

@section('content')

<div class="container-fluid">

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="section-heading mb-1">Notifications</h2>
            <p class="section-excerpt mb-0">You have {{ $unreadNotifications ?? 0 }} unread messages.</p>
        </div>

        <form method="POST" action="{{ route('admin.notifications.read-all') }}" class="mb-0">
            @csrf
            <button class="btn btn-success">Mark All Read</button>
        </form>
    </div>

    <div class="card border-0 shadow-sm rounded-4 admin-card">

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($notifications as $notification)

                        <tr>

                            <td>
                                {{ $notification->title }}
                            </td>

                            <td>
                                {{ $notification->message }}
                            </td>

                            <td>

                                @if($notification->is_read)

                                <span class="badge bg-success">
                                    Read
                                </span>

                                @else

                                <span class="badge bg-danger">
                                    Unread
                                </span>

                                @endif

                            </td>

                            <td>
                                {{ $notification->created_at }}
                            </td>

                            <td>
                                @if(!$notification->is_read)
                                <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary">Mark Read</button>
                                </form>
                                @else
                                <span class="text-muted">Done</span>
                                @endif
                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="5">
                                No Notifications
                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>
        </div>

    </div>

</div>

@endsection