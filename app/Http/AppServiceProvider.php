<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
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
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        // Admin gate — Owner and Manager can manage products, inventory, etc.
        // This makes @can('admin') work in Blade views.
        Gate::define('admin', function ($user) {
            return in_array($user->role, ['Owner', 'Manager']);
        });
    }
}
