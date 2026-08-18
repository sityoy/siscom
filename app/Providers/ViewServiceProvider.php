<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Notification;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('*', function ($view) {

            $notificationCount = 0;

            if (auth()->check()) {

                if (auth()->user()->hasRole('client')) {

                    $client = auth()->user()->client;

                    if ($client) {

                        $notificationCount =
                            Notification::where(
                                'client_id',
                                $client->id
                            )
                            ->where(
                                'is_read',
                                false
                            )
                            ->count();
                    }

                }

            }

            $view->with(
                'notificationCount',
                $notificationCount
            );

        });
    }
}
