<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Affectation;
use Exception;
use Illuminate\Support\Facades\DB;

class ArticleService
{
    public function supprimer(Article $article): void
{
    

    // On archive uniquement ce qui reste en stock.
    $article->update([
        'statut'   => 'Réformé',
        'etat'     => 'Réformé',
        'quantite' => 0,
    ]);
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

        // une quantité négative n'est pas autorisée
        if (!is_null($quantite) && $quantite < 0) {
        throw new Exception("La quantité en stock ne peut pas être négative.");
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