<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::latest()->paginate(20);
        $unreadNotifications = Notification::where('is_read', false)->count();

        return view(
            'admin.notifications.index',
            compact('notifications', 'unreadNotifications')
        );
    }

    public function markAllRead()
    {
        Notification::query()
            ->update([
                'is_read' => true
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update([
            'is_read' => true,
        ]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function unreadCount()
    {
        return response()->json([
            'unread_count' => Notification::where('is_read', false)->count(),
        ]);
    }
}
