<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index()
    {
        $client = auth()->user()->client;

        if (!$client) {

            abort(403);

        }

        $notifications = $client->notifications()
            ->latest()
            ->paginate(10);

        $client->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true
            ]);

        return view(
            'clients.notifications.index',
            compact('notifications')
        );
    }
}
