<?php

namespace App\Providers;

use App\Models\Alerte;
use App\Observers\AlerteObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Alerte::observe(AlerteObserver::class);
    }
}
