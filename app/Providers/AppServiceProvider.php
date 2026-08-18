<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Gate::define('super_admin', function ($user) {

            return $user->hasRole('super_admin');

        });

        Gate::define('client', function ($user) {

            return $user->hasRole('client');

        });
    }
}
