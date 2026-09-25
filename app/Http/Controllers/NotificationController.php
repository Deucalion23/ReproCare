<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Get unread notifications count (for AJAX)
    public function unreadCount()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['count' => 0]);
        }
        $count = $user->notifications()->unread()->count();
        return response()->json(['count' => $count]);
    }

    // Get recent notifications (for dropdown)
    public function recent()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['notifications' => []]);
        }
        $notifications = $user->notifications()
            ->with('patientAlert:id,is_read,read_at,resolved_at')
            ->orderByRaw('COALESCE(last_reminded_at, created_at) DESC')
            ->take(5)
            ->get();

        return response()->json(['notifications' => $notifications]);
    }

    // Mark notification as read
    public function markAsRead($id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }
        $notification = Notification::where('user_id', $user->id)->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    // Mark all notifications as read
    public function markAllAsRead()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }
        $user->notifications()->unread()->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }

    // Delete notification
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }
        $notification = Notification::where('user_id', $user->id)->findOrFail($id);

        $notification->markAsRead();
        $notification->delete();

        return response()->json(['success' => true]);
    }

    // Admin functions for midwives (create notifications)
    public function create()
    {
        if (!auth()->check() || auth()->user()->role !== 'midwife') {
            abort(403);
        }

        return view('midwife.notifications.create');
    }

    public function store(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'midwife') {
            abort(403);
        }

        $request->validate([
            'patient_id' => 'required|exists:users,id',
            'message' => 'required|string|max:500',
        ]);

        Notification::createNotification($request->patient_id, $request->message);

        return redirect()->route('midwife.dashboard')
            ->with('success', 'Notification sent successfully');
    }
}
