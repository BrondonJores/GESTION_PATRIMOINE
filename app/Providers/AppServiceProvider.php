<?php

namespace App\Providers;

use App\Models\Alerte;
use App\Models\User;
use App\Observers\AlerteObserver;
use App\Observers\UserObserver;
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
        User::observe(UserObserver::class);
    }
}
