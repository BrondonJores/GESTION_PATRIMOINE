<?php

namespace App\Observers;

use App\Models\Alerte;
use App\Models\Article;
use App\Models\Stock;

class StockObserver
{
    /**
     * Après chaque modification d'une ligne stock.
     * Gère : archivage automatique + alertes.
     * Uniquement si le statut modifié est Disponible ou Maintenance.
     */
    public function updated(Stock $stock): void
    {
        $article = Article::find($stock->article_id);
        if (!$article) return;

        // ──  Archivage automatique ──────────────────────────────
        // Si Disponible=0, Affecté=0, Maintenance=0 → archiver
        if (!$article->is_archived) {
            $disponible  = Stock::quantitePour($article->id, Stock::DISPONIBLE);
            $affecte     = Stock::quantitePour($article->id, Stock::AFFECTE);
            $maintenance = Stock::quantitePour($article->id, Stock::MAINTENANCE);

            if ($disponible === 0 && $affecte === 0 && $maintenance === 0) {
                $article->update(['is_archived' => true]);
                return; 
            }
        }

        //  Alerte stock — uniquement sur le stock Disponible 
        if ($stock->statut !== Stock::DISPONIBLE) return;
        if ($article->is_archived) return;
        if (is_null($article->quantite_min)) return;

        $disponible  = Stock::quantitePour($article->id, Stock::DISPONIBLE);
        $seuilMin    = (int) $article->quantite_min;
        $seuilFaible = $seuilMin * 2;

        // Alerte uniquement si le stock a DIMINUÉ (pas lors d'une récupération)
        $dirty = $stock->getDirty();
        if (isset($dirty['quantite'])) {
            $ancienneQ = (int) $stock->getOriginal('quantite');
            $nouvelleQ = (int) $stock->quantite;
            // Si le stock monte → pas d'alerte
            if ($nouvelleQ >= $ancienneQ) return;
        }

        if ($disponible === 0) {
            $canal  = 'Tous';
            $retour = "Stock ÉPUISÉ : aucune unité disponible pour {$article->designation}.";
        } elseif ($disponible <= $seuilMin) {
            $canal  = 'Tous';
            $retour = "Stock MINIMAL atteint : {$disponible} unité(s) — seuil : {$seuilMin}.";
        } elseif ($disponible <= $seuilFaible) {
            $canal  = 'InApp';
            $retour = "Stock FAIBLE : {$disponible} unité(s) disponible(s).";
        } else {
            return;
        }

        $existe = Alerte::where('article_id', $article->id)
                        ->where('statut', 'Non_traité')
                        ->exists();
        if ($existe) return;

        Alerte::create([
            'article_id'  => $article->id,
            'statut'      => 'Non_traité',
            'canal'       => $canal,
            'retour'      => $retour,
            'date_alerte' => now(),
        ]);
    }
}