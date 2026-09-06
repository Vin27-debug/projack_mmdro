<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::visibleTo(Auth::id())->latest()->paginate(20);
        $unreadNotifications = Notification::visibleTo(Auth::id())
            ->where('is_read', false)
            ->count();

        return view(
            'admin.notifications.index',
            compact('notifications', 'unreadNotifications')
        );
    }

    public function markAllRead()
    {
        Notification::query()
            ->visibleTo(Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function markAsRead(Notification $notification)
    {
        $this->authorizeNotification($notification);

        $notification->update([
            'is_read' => true,
        ]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function open(Notification $notification)
    {
        $this->authorizeNotification($notification);

        $notification->update([
            'is_read' => true,
        ]);

        return redirect()->route('admin.notifications.index');
    }

    public function unreadCount()
    {
        return response()->json([
            'unread_count' => Notification::visibleTo(Auth::id())
                ->where('is_read', false)
                ->count(),
        ]);
    }

    protected function authorizeNotification(Notification $notification): void
    {
        if ($notification->user_id !== null && (int) $notification->user_id !== (int) Auth::id()) {
            abort(403);
        }
    }
}
