<?php

namespace App\Providers;

use App\Models\Affectation;
use App\Observers\AffectationObserver;
use App\Policies\AffectationPolicy;
use App\Services\AffectationService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AffectationService::class, function ($app) {
            return new AffectationService();
        });
    }

    public function boot(): void
    {
        Affectation::observe(AffectationObserver::class);
        Gate::policy(Affectation::class, AffectationPolicy::class);
    }
}