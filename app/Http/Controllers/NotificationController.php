<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of notifications.
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount()
    {
        $count = Auth::user()->unreadNotifications->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Send test notification.
     */
    public function sendTest(Request $request)
    {
        $request->validate([
            'type' => 'required|in:sms,email',
            'message' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        if ($request->type === 'sms') {
            $this->notificationService->sendSMS($user->phone, $request->message);
        } else {
            $this->notificationService->sendEmail(
                $user->email,
                'Test Notification',
                $request->message
            );
        }

        return response()->json(['success' => true, 'message' => 'Test notification sent successfully']);
    }

    /**
     * Send bulk notifications.
     */
    public function sendBulk(Request $request)
    {
        $request->validate([
            'type' => 'required|in:sms,email',
            'message' => 'required|string|max:500',
            'roles' => 'array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $roles = $request->roles ?? [];
        $this->notificationService->notifyStaff($request->message, $roles);

        return response()->json(['success' => true, 'message' => 'Bulk notifications sent successfully']);
    }
}