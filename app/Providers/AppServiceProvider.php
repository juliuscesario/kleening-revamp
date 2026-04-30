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

        view()->composer('*', function ($view) {
            if (auth()->check() && in_array(auth()->user()->role, ['admin', 'owner'])) {
                $diskTotal   = disk_total_space('/');
                $diskFree    = disk_free_space('/');
                $diskUsed    = $diskTotal - $diskFree;
                $diskPercent = round(($diskUsed / $diskTotal) * 100);
                $diskUsedGB  = round($diskUsed / 1073741824, 1);
                $diskTotalGB = round($diskTotal / 1073741824, 1);

                $view->with(compact('diskPercent', 'diskUsedGB', 'diskTotalGB'));
            }
        });
    }
}