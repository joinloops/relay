<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('about', function () {
            return 'A content discovery service, created by https://joinloops.org.';
        });

        $this->app->singleton('user_agent', function () {
            $url = config('app.url');

            return "LoopsRelay/1.0.0 (+{$url})";
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('relay-admin', fn ($user) => (bool) ($user->is_admin ?? false));
    }
}
