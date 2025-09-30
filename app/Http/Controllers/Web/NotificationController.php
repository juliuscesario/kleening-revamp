<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display all of the user's notifications.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // Paginate all notifications
        $notifications = $user->notifications()->paginate(20);

        // Mark all unread notifications as read when the user visits this page
        $user->unreadNotifications->markAsRead();

        return view('pages.notifications.index', compact('notifications'));
    }
}
