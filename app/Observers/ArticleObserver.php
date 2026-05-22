<?php
// app/Observers/ArticleObserver.php

namespace App\Observers;

use App\Models\Article;
use App\Models\AuditLog as LogModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ArticleObserver
{
    public function created(Article $article): void
    {
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