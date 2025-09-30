<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'read' => $user->readNotifications,
            'unread' => $user->unreadNotifications,
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(['status' => 'success']);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();
        return response()->json(['status' => 'success']);
    }
}
