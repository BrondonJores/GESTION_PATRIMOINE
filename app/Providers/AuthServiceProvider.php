<?php

namespace App\Providers;

use App\Models\Article;
use App\Policies\ArticlePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }
 protected $policies = [
        Article::class => ArticlePolicy::class,
    ];

    
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
            $this->registerPolicies();
    }
}
