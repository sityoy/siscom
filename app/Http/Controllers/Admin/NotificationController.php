<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $unreadCount = AdminNotification::where(
            'is_read',
            false
        )->count();

        $notifications = AdminNotification::latest()
            ->paginate(10);

        // AdminNotification::where(
        //     'is_read',
        //     false
        // )->limit(20)
        // ->update([
        //     'is_read' => true
        // ]);

       return view(
            'admin.notifications.index',
            compact(
                'notifications',
                'unreadCount'
            )
        );
    }

    public function destroy(
        AdminNotification $notification
    ){
        $notification->delete();

        return back();
    }
}
