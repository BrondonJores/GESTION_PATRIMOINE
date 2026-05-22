<?php

namespace App\Providers;

use App\Models\Affectation;
use App\Models\Alerte;
use App\Models\Article;
use App\Models\Consommable;
use App\Models\User;
use App\Observers\AffectationObserver;
use App\Observers\AlerteObserver;
use App\Observers\ArticleObserver;
use App\Observers\ConsommableObserver;
use App\Observers\UserObserver;
use App\Policies\AffectationPolicy;
use App\Policies\RolePolicy;
use App\Services\AffectationService;
use App\Services\ArticleService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

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
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Affectation::class, AffectationPolicy::class);

        Article::observe(ArticleObserver::class);
        Affectation::observe(AffectationObserver::class);
        Alerte::observe(AlerteObserver::class);
        Consommable::observe(ConsommableObserver::class);
        User::observe(UserObserver::class);
    }
}
