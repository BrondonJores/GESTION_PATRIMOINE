<?php

namespace App\Providers;

use App\Models\Affectation;
use App\Models\Article;
use App\Observers\AffectationObserver;
use App\Policies\AffectationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Observers\ArticleObserver;
use App\Services\ArticleService;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
         $this->app->singleton(ArticleService::class);
    }

    public function boot(): void
    {
        Article::observe(ArticleObserver::class);        
        Affectation::observe(AffectationObserver::class);
        Gate::policy(Affectation::class, AffectationPolicy::class);

    }
}
