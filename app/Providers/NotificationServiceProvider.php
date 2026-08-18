<?php

namespace App\Providers;

use App\Models\Notification;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function ($view) {

            $clientNotificationCount = 0;
            $adminNotificationCount = 0;

            if (auth()->check()) {

                if (auth()->user()->hasRole('client')) {

                    $client = auth()->user()->client;

                    if ($client) {

                        $clientNotificationCount =
                            Notification::where(
                                'client_id',
                                $client->id
                            )
                            ->where('is_read', false)
                            ->count();
                    }
                }

                if (
                    auth()->user()->hasRole('super_admin')
                    || auth()->user()->hasRole('admin')
                ) {

                    $adminNotificationCount =
                        AdminNotification::where(
                            'is_read',
                            false
                        )->count();
                }
            }

            $view->with(
                'clientNotificationCount',
                $clientNotificationCount
            );

            $view->with(
                'adminNotificationCount',
                $adminNotificationCount
            );
        });
    }
}
