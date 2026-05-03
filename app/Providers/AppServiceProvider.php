<?php

namespace App\Providers;

use App\Models\Affectation;
use App\Models\Article;
use App\Observers\AffectationObserver;
use App\Policies\AffectationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        
        Affectation::observe(AffectationObserver::class);
        Gate::policy(Affectation::class, AffectationPolicy::class);
    }
}