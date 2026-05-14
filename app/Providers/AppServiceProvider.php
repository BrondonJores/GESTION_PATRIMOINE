<?php

namespace App\Providers;

use App\Models\Alerte;
use App\Models\Article;
use App\Models\User;
use App\Observers\AlerteObserver;
use App\Observers\ArticleObserver;
use App\Observers\UserObserver;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

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
        Gate::policy(Role::class, RolePolicy::class);

        Alerte::observe(AlerteObserver::class);
        Article::observe(ArticleObserver::class);
        User::observe(UserObserver::class);
    }
}
