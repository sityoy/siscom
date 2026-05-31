<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = AdminNotification::latest()
            ->paginate(10);

        AdminNotification::where(
            'is_read',
            false
        )->update([
            'is_read' => true
        ]);

        return view(
            'admin.notifications.index',
            compact('notifications')
        );
    }
}
