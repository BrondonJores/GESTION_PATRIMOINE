<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\Alerte;
use App\Models\AuditLog as LogModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ArticleObserver
{
    
     //après création d'un article.
     
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

    // après modification d'un article.
     
    public function updated(Article $article): void
    {
        $dirty    = $article->getDirty();    // champs modifiés → nouvelles valeurs
        $original = $article->getOriginal(); // valeurs avant modification

        // Log uniquement si des champs ont vraiment changé
         if (!empty($dirty)) {
            LogModel::create([
                'module'      => 'Articles',
                'action'      => 'Modification',
                'adresse_ip'  => Request::ip(),
                'user_id'     => Auth::id(),
                'date_action' => now(),
            ]);
        }

        
    }


    //avant suppression d'un article.
    
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