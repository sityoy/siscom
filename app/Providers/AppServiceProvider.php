<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (
            $this->app->environment('production')
            && str_starts_with((string) config('app.url'), 'https://')
        ) {
            URL::forceScheme('https');
        }

        Gate::define('super_admin', function ($user) {

            return $user->hasRole('super_admin');

        });

        Gate::define('admin_panel', function ($user) {

            return $user->hasAnyRole(['super_admin', 'admin']);

        });

        Gate::define('client', function ($user) {

            return $user->hasRole('client');

        });
    }
}
