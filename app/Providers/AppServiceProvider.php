<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Article;
use App\Observers\ArticleObserver;
use App\Services\ArticleService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
         $this->app->singleton(ArticleService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
          //déclenche automatique des actions aprés CRUB sur articles
          Article::observe(ArticleObserver::class);
    }
}
