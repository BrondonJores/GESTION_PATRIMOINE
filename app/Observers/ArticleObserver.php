<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\Stock;
use App\Models\AuditLog as LogModel;
use App\Services\StockAlertService;
use App\Services\StockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ArticleObserver
{
    public function __construct(
        private readonly StockAlertService $stockAlertService,
    ) {
    }

    public function created(Article $article): void
    {
        app(StockService::class)->initialiser($article);

        LogModel::create([
            'module'      => 'Articles',
            'action'      => 'Création',
            'adresse_ip'  => Request::ip(),
            'user_id'     => Auth::id(),
            'date_action' => now(),
        ]);

        $this->stockAlertService->synchroniserPourArticle($article);
    }

    public function updated(Article $article): void
    {
        LogModel::create([
            'module'      => 'Articles',
            'action'      => 'Modification',
            'adresse_ip'  => Request::ip(),
            'user_id'     => Auth::id(),
            'date_action' => now(),
        ]);

        if ($article->wasChanged(['quantite', 'quantite_min'])) {
            $this->stockAlertService->synchroniserPourArticle($article);
        }
    }

    public function deleting(Article $article): void
    {
        LogModel::create([
            'module'      => 'Articles',
            'action'      => 'Suppression',
            'adresse_ip'  => Request::ip(),
            'user_id'     => Auth::id(),
            'date_action' => now(),
        ]);
    }
}