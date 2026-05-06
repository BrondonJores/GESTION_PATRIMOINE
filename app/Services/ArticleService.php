<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Affectation;
use Exception;
use Illuminate\Support\Facades\DB;

class ArticleService
{
    /**
     * Supprimer un article:
     * Si l'article a des affectations dans l'historique → on ne supprime pas
     * physiquement. Il passe au statut Réformé (archivage logique).
     * Suppression physique seulement si aucun mouvement n'existe.
     */
    public function supprimer(Article $article): void
    {
        $aDesAffectations = Affectation::where('article_id', $article->id)->exists();

        if ($aDesAffectations) {
            // Suppression logique 
            $article->update(['statut' => 'Réformé', 'etat' => 'Réformé']);
        } else {
            // Aucun historique → suppression physique autorisée
            $article->delete();
        }
    }

    /**
     * Valider les règles métier avant création ou modification.
     * @throws Exception si une règle est violée
     */

    public function valider(array $data, ?Article $article = null): void
    {
        $quantite    = $data['quantite']    ?? null;
        $quantiteMin = $data['quantite_min'] ?? null;

        // quantite_min < quantite
        if (!is_null($quantiteMin) && !is_null($quantite)) {
            if ($quantiteMin >= $quantite) {
                throw new Exception(
"Le seuil minimal ({$quantiteMin}) doit être inférieur à la quantité en stock ({$quantite})."                );
            }
        }

        // lors d'une modification, la nouvelle quantité ne peut pas
        // être inférieure aux unités déjà affectées (elles sont hors stock)
        if ($article && !is_null($quantite)) {
            $quantiteAffectee = Affectation::where('article_id', $article->id)
                ->whereNull('date_recuperation') // affectations actives (non récupérées)
                ->sum('quantite');

            if ($quantite < $quantiteAffectee) {
                throw new Exception(
"{$quantiteAffectee} unité(s) sont actuellement affectées et ne peuvent pas être retirées du stock."                );
            }  
        }


    }

    /**
     * Statistiques globales pour le tableau de bord.
     */
    public function getStatistiques(): array
    {
        return [
            'total'          => Article::count(),
            'disponibles'    => Article::where('statut', 'Disponible')->count(),
            'affectes'       => Article::where('statut', 'Affecté')->count(),
            'en_maintenance' => Article::where('statut', 'En_maintenance')->count(),
            'reformes'       => Article::where('statut', 'Réformé')->count(),
            'sous_seuil'     => Article::whereNotNull('quantite_min')
                                       ->whereColumn('quantite', '<=', 'quantite_min')
                                       ->count(),
        ];
    }
}