<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Stock;
use Exception;
use Illuminate\Support\Facades\DB;

class ArticleService
{
    public function valider(array $data, ?Article $article = null): void
    {
        $quantite    = isset($data['quantite_totale'])
                       ? (int) $data['quantite_totale']
                       : null;
        $quantiteMin = isset($data['quantite_min'])
                       ? (int) $data['quantite_min']
                       : null;

        if (!is_null($quantite) && $quantite <= 0) {
            throw new Exception("La quantité totale doit être supérieure à zéro.");
        }

        if (!is_null($quantiteMin) && !is_null($quantite)) {
            if ($quantiteMin >= $quantite) {
                throw new Exception(
                    "Le seuil minimal ({$quantiteMin}) doit être " .
                    "inférieur à la quantité totale ({$quantite})."
                );
            }
        }

        // Lors d'une modification : vérifier que la nouvelle quantité totale
        // ne descend pas en dessous des unités déjà sorties (affectées + maintenance + réformées)
        if ($article && !is_null($quantite)) {
            $stocksOccupes = Stock::quantitePour($article->id, Stock::AFFECTE)
                           + Stock::quantitePour($article->id, Stock::MAINTENANCE)
                           + Stock::quantitePour($article->id, Stock::REFORME);

            if ($quantite < $stocksOccupes) {
                throw new Exception(
                    "La quantité totale ({$quantite}) ne peut pas être inférieure " .
                    "aux unités déjà sorties — affectées + maintenance + réformées : {$stocksOccupes}."
                );
            }
        }
    }


 /**
 * Synchroniser le stock Disponible après modification de quantite_totale.
 *
 * Nouvelle logique métier :
 *   disponible = total - affecté - maintenance - réformé
 *
 * Les stocks Affecté / Maintenance / Réformé ne changent jamais ici —
 * ils représentent des unités déjà utilisées, indisponibles ou hors service.
 */
public function synchroniserStockDisponible(Article $article): void
{
    DB::transaction(function () use ($article) {
        $affecte     = Stock::quantitePour($article->id, Stock::AFFECTE);
        $maintenance = Stock::quantitePour($article->id, Stock::MAINTENANCE);
        $reforme     = Stock::quantitePour($article->id, Stock::REFORME);

        $nouveauDisponible = $article->quantite_totale
                           - $affecte
                           - $maintenance
                           - $reforme;

        if ($nouveauDisponible < 0) {
            throw new Exception(
                "La quantité totale ({$article->quantite_totale}) est inférieure " .
                "aux unités déjà sorties — affectées ({$affecte}) + " .
                "maintenance ({$maintenance}) + réformées ({$reforme})."
            );
        }

        Stock::updateOrCreate(
            ['article_id' => $article->id, 'statut' => Stock::DISPONIBLE],
            ['quantite'   => $nouveauDisponible]
        );
    });
}
    public function getStatistiques(): array
    {
        // Sous seuil = articles actifs dont stock Disponible <= quantite_min
        $sousSeuilCount = Article::where('is_archived', false)
            ->whereNotNull('quantite_min')
            ->get()
            ->filter(fn ($a) =>
                Stock::quantitePour($a->id, Stock::DISPONIBLE) <= (int) $a->quantite_min
            )->count();

        return [
            'total'      => Article::count(),
            'actifs'     => Article::where('is_archived', false)->count(),
            'archives'   => Article::where('is_archived', true)->count(),
            'sous_seuil' => $sousSeuilCount,
        ];
    }
}