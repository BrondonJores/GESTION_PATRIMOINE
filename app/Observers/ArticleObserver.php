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

            // Si la quantité a diminué → vérifier les seuils
    if (array_key_exists('quantite', $article->getDirty()) && $article->statut !== 'Réformé') {
        $nouvelle = (int) $article->quantite;
        $ancienne = (int) $article->getOriginal('quantite');
        $seuilMin = $article->quantite_min;

        if ($nouvelle >= $ancienne) return; // stock monte = pas d'alerte
        if (is_null($seuilMin))     return; // pas de seuil = pas d'alerte

        $seuilFaible = $seuilMin * 2;

        if ($nouvelle === 0) {
            $canal  = 'Tous';
            $retour = "Stock ÉPUISÉ : aucune unité disponible pour {$article->designation}.";

        } elseif ($nouvelle <= $seuilMin) {
            $canal  = 'Tous';
            $retour = "Stock MINIMAL atteint : {$nouvelle} unité(s) pour {$article->designation}.";

        } elseif ($nouvelle <= $seuilFaible) {
            $canal  = 'InApp';
            $retour = "Stock FAIBLE : {$nouvelle} unité(s) pour {$article->designation}.";

        } else {
            return;
        }

        $dejaExistante = Alerte::where('article_id', $article->id)
            ->where('statut', 'Non_traité')
            ->exists();

        if (!$dejaExistante) {
            Alerte::create([
                'article_id'  => $article->id,
                'statut'      => 'Non_traité',
                'canal'       => $canal,
                'retour'      => $retour,
                'date_alerte' => now(),
            ]);
        }
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