<?php

namespace App\Observers;

use App\Models\Article;
use App\Services\StockAlertService;

class ArticleObserver
{
    public function __construct(
        private readonly StockAlertService $stockAlertService,
    ) {
    }

    public function created(Article $article): void
    {
        $this->stockAlertService->synchroniserPourArticle($article);
    }

    public function updated(Article $article): void
    {
        if (! $article->wasChanged(['quantite', 'quantite_min'])) {
            return;
        }

        $this->stockAlertService->synchroniserPourArticle($article);
    }
}
