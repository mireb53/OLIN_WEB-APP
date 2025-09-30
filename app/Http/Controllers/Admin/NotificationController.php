<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        $unread = Notification::where('user_id', $user->id)->where('is_read', false)->count();

        if ($request->wantsJson()) {
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unread,
            ]);
        }

        return view('admin.notifications.index', compact('notifications', 'unread'));
    }

    public function all()
    {
        $user = Auth::user();
        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate(20);
        $unread = Notification::where('user_id', $user->id)->where('is_read', false)->count();

        return view('admin.notifications.all', compact('notifications', 'unread'));
    }

    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)->where('id', $id)->firstOrFail();
        $notification->update(['is_read' => true]);

        $unread = Notification::where('user_id', $user->id)->where('is_read', false)->count();

        return response()->json(['success' => true, 'unread_count' => $unread]);
    }
}
