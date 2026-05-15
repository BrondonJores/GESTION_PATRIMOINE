<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\Stock;
use App\Services\StockService;
use App\Models\AuditLog as LogModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ArticleObserver
{
    
     //après création d'un article.

public function created(Article $article): void
{
    // Initialiser les lignes de stock
    app(StockService::class)->initialiser($article);
    Stock::create([
    'article_id' => $article->id,
    'statut' => 'Disponible',
    'quantite' => $article->quantite_totale,
]);
    LogModel::create([
        'module'      => 'Articles',
        'action'      => 'Création',
        'adresse_ip'  => Request::ip(),
        'user_id'     => Auth::id(),
        'date_action' => now(),
    ]);
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