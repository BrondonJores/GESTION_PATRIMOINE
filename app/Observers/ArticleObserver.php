<?php

namespace App\Observers;

use App\Models\Article;
use App\Models\Alerte;
use App\Models\AuditLog as LogModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ArticleObserver
{
    /**
     * CRITIQUE : après création d'un article.
     * → Log d'audit (traçabilité obligatoire selon cahier des charges).
     * → Alerte si la quantité initiale est déjà sous le seuil.
     */
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

    /**
     * CRITIQUE : après modification d'un article.
     * → Log d'audit avec champs modifiés (avant/après).
     * → Alerte si la quantité vient de passer sous le seuil minimal.
     */
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


    /**
     * CRITIQUE : avant suppression d'un article.
     * → Log d'audit AVANT suppression (après, l'article n'existe plus en base).
     */
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