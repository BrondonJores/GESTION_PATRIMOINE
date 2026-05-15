<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Stock;
use Exception;
use Illuminate\Support\Facades\DB;

class StockService
{
    
    // INITIALISATION —> à la création d'un article uniquement
    public function initialiser(Article $article): void
    {
        
        Stock::firstOrCreate(
            ['article_id' => $article->id, 'statut' => Stock::DISPONIBLE],
            ['quantite'   => (int) $article->quantite_totale]
        );

    }

    
    // MISE EN MAINTENANCE : Disponible → En_maintenance
    
    public function mettreEnMaintenance(Article $article, int $quantite): void
    {
        if ($article->is_archived) {
            throw new Exception("Impossible : l'article est archivé.");
        }

        DB::transaction(function () use ($article, $quantite) {
            Stock::deplacer(
                $article->id,
                Stock::DISPONIBLE,
                Stock::MAINTENANCE,
                $quantite
            );
        });
    }


    // REMISE EN SERVICE : En_maintenance → Disponible
    
    public function remettreEnService(Article $article, int $quantite): void
    {
        DB::transaction(function () use ($article, $quantite) {
            Stock::deplacer(
                $article->id,
                Stock::MAINTENANCE,
                Stock::DISPONIBLE,
                $quantite
            );

            if ($article->is_archived) {
                $article->update(['is_archived' => false]);
            }
        });
    }


    // RÉFORME PARTIELLE
    // Disponible → Réformé  OU  En_maintenance → Réformé

    public function reformer(
        Article $article,
        string  $statutSource,
        int     $quantite,
        string  $motif
    ): void {
        // Stock Affecté ne peut JAMAIS être réformé directement
        if ($statutSource === Stock::AFFECTE) {
            throw new Exception(
                "Impossible de réformer un stock affecté directement. " .
                "L'article doit d'abord être récupéré."
            );
        }

        if (!in_array($statutSource, [Stock::DISPONIBLE, Stock::MAINTENANCE])) {
            throw new Exception("Statut source invalide.");
        }

        DB::transaction(function () use ($article, $statutSource, $quantite, $motif) {
            Stock::deplacer(
                $article->id,
                $statutSource,
                Stock::REFORME,
                $quantite
            );

            $article->update([
                'observations' => trim(
                    ($article->observations ?? '') .
                    "\n[RÉFORME x{$quantite} depuis {$statutSource}] {$motif}"
                ),
            ]);

            // L'archivage automatique est géré par StockObserver
        });
    }

// RÉINTÉGRATION : Réformé → Disponible
// RÈGLE : la quantité à réintégrer ne peut pas dépasser le stock actuellement en statut Réformé.

public function reintegrer(Article $article, int $quantite, string $motif): void
{
    DB::transaction(function () use ($article, $quantite, $motif) {
        $stockReforme = Stock::quantitePour($article->id, Stock::REFORME);

        if ($quantite <= 0) {
            throw new Exception("La quantité à réintégrer doit être supérieure à zéro.");
        }

        if ($quantite > $stockReforme) {
            throw new Exception(
                "Impossible de réintégrer {$quantite} unité(s). " .
                "Stock réformé disponible : {$stockReforme}."
            );
        }

        // Réformé ↓ — Disponible ↑
        Stock::deplacer(
            $article->id,
            Stock::REFORME,
            Stock::DISPONIBLE,
            $quantite
        );

        // Tracer le motif dans observations
        $article->update([
            'observations' => trim(
                ($article->observations ?? '') .
                "\n[RÉINTÉGRATION x{$quantite}] {$motif}"
            ),
        ]);

        // Si l'article était archivé, le désarchiver automatiquement

        if ($article->is_archived) {
            $article->update(['is_archived' => false]);
        }
    });
}
    // ARCHIVAGE MANUEL (bouton Archiver)
    // Condition : Affecté = 0

    public function archiverManuellement(
        Article $article,
        string  $motif = 'Archivage administratif'
    ): void {
        DB::transaction(function () use ($article, $motif) {
            // Vérifier qu'aucune unité n'est affectée
            $affecte = Stock::quantitePour($article->id, Stock::AFFECTE);
            if ($affecte > 0) {
                throw new Exception(
                    "Impossible d'archiver : {$affecte} unité(s) encore affectée(s). " .
                    "Récupérez-les d'abord."
                );
            }

            // Réformer Disponible
            $disponible = Stock::quantitePour($article->id, Stock::DISPONIBLE);
            if ($disponible > 0) {
                Stock::deplacer($article->id, Stock::DISPONIBLE, Stock::REFORME, $disponible);
            }

            // Réformer Maintenance
            $maintenance = Stock::quantitePour($article->id, Stock::MAINTENANCE);
            if ($maintenance > 0) {
                Stock::deplacer($article->id, Stock::MAINTENANCE, Stock::REFORME, $maintenance);
            }

            $article->update([
                'is_archived'  => true,
                'observations' => trim(
                    ($article->observations ?? '') . "\n[ARCHIVAGE] {$motif}"
                ),
            ]);
        });
    }
}