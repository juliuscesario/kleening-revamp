<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

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
        Paginator::useBootstrapFive();

        // Share current tenant with all views
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (app()->has('currentTenant')) {
                $view->with('currentTenant', app('currentTenant'));
            }
        });

        // Use custom PersonalAccessToken model for multi-tenancy
        \Laravel\Sanctum\Sanctum::usePersonalAccessTokenModel(\App\Models\PersonalAccessToken::class);
    }
}