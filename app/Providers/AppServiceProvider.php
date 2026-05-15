<?php

namespace App\Providers;

use App\Models\Affectation;
use App\Models\Article;
use App\Models\Stock;
use App\Observers\AffectationObserver;
use App\Policies\AffectationPolicy;
use App\Services\AffectationService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Observers\ArticleObserver;
use App\Observers\StockObserver;
use App\Services\ArticleService;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
         $this->app->singleton(ArticleService::class);

        $this->app->singleton(AffectationService::class, function ($app) {
            return new AffectationService();
        });
    }

    public function boot(): void
    {

        Article::observe(ArticleObserver::class);        
        Stock::observe(StockObserver::class);
        Affectation::observe(AffectationObserver::class);
        Gate::policy(Affectation::class, AffectationPolicy::class);

    }
}
