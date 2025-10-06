<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get recent notifications (last 30 days)
        $notifications = Notification::where('user_id', $user->id)
            ->orWhere('client_id', function($query) use ($user) {
                $query->select('id')
                    ->from('clients')
                    ->where('created_by', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->category,
                    'created_at' => $notification->created_at,
                    'is_read' => $notification->read_at !== null,
                ];
            });

        $unreadCount = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|exists:notifications,id'
        ]);

        $notification = Notification::where('id', $request->notification_id)
            ->where('user_id', auth()->id())
            ->first();

        if ($notification) {
            $notification->update(['read_at' => now()]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
