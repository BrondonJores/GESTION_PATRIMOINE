<?php

namespace App\Services;

use App\Models\Affectation;
use App\Models\Article;
use App\Models\Recuperation;
use App\Models\Reaffectation;
use App\Models\Stock;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AffectationService
{
    // AFFECTER : Disponible → Affecté
    // - On appelle Stock::deplacer() pour déplacer les unités
    //   de la ligne Disponible vers la ligne Affecté dans la table stocks
    // - La vérification de disponibilité se fait sur la table stocks,
    //   pas sur article.quantite
public function affecter(array $data): Affectation
{
    return DB::transaction(function () use ($data) {
        $article = Article::findOrFail($data['article_id']);

        if ($article->is_archived) {
            throw new Exception("Impossible d'affecter un article archivé.");
        }

        $disponible = Stock::quantitePour($article->id, Stock::DISPONIBLE);

        if ($data['quantite'] > $disponible) {
            throw new Exception(
                "Stock insuffisant. Disponible : {$disponible}, " .
                "demandé : {$data['quantite']}."
            );
        }

        // ── VÉRIFICATION CAPACITÉ DE LA SALLE ──────────────
        if (!empty($data['salle_id'])) {
            $salle = \App\Models\Salle::find($data['salle_id']);
            if ($salle && $salle->capacite) {
                $dejaDansSalle = Affectation::where('salle_id', $salle->id)
                    ->whereNull('date_recuperation')
                    ->sum('quantite');

                $totalApres = $dejaDansSalle + $data['quantite'];

                if ($totalApres > $salle->capacite) {
                    throw new Exception(
                        "Capacité de la salle dépassée. Capacité : {$salle->capacite}, " .
                        "déjà affecté : {$dejaDansSalle}, demandé : {$data['quantite']}. " .
                        "Disponible : " . ($salle->capacite - $dejaDansSalle) . " place(s)."
                    );
                }
            }
        }

        $affectation = Affectation::create([
            'article_id'       => $article->id,
            'bloc_id'          => $data['bloc_id'],
            'salle_id'         => $data['salle_id'] ?? null,
            'quantite'         => $data['quantite'],
            'observations'     => $data['observations'] ?? null,
            'date_affectation' => $data['date_affectation'] ?? now()->toDateString(),
            'user_id'          => Auth::id(),
        ]);

        Stock::deplacer(
            $article->id,
            Stock::DISPONIBLE,
            Stock::AFFECTE,
            $data['quantite']
        );

        return $affectation;
    });
}
    
    // RÉCUPÉRER : Affecté → Disponible
    // Ce qui change :
    // - On appelle Stock::deplacer() Affecté → Disponible
    // - Si l'article était archivé (is_archived), il se désarchive
    //   automatiquement car du stock redevient disponible
    // cette règle est inutile car un artcile qui a des affectations n'est jamais archivé!

    public function recuperer(Affectation $affectation, array $data): Recuperation
    {
        return DB::transaction(function () use ($affectation, $data) {
            if (!is_null($affectation->date_recuperation)) {
                throw new Exception("Cette affectation a déjà été récupérée.");
            }

            if ($data['quantite'] > $affectation->quantite) {
                throw new Exception (
                    "Impossible de récupérer {$data['quantite']} unité(s). " .
                    "L'affectation concerne {$affectation->quantite} unité(s)."
                );
            }

            $recuperation = Recuperation::create([
                'affectation_id'    => $affectation->id,
                'quantite'          => $data['quantite'],
                'observations'      => $data['observations'] ?? null,
                'date_recuperation' => $data['date_recuperation'] ?? now()->toDateString(),
            ]);

            // Récupération partielle : réduire la quantité de l'affectation
            // Récupération totale : marquer l'affectation comme terminée
            $quantiteRestante = $affectation->quantite - $data['quantite'];

            if ($quantiteRestante > 0) {
                $affectation->update(['quantite' => $quantiteRestante]);
            } else {
                $affectation->update([
                    'date_recuperation' => $data['date_recuperation'] ?? now()->toDateString(),
                ]);
            }

            //  Déplacer le stock : Affecté ↓ — Disponible ↑
            Stock::deplacer(
                $affectation->article_id,
                Stock::AFFECTE,
                Stock::DISPONIBLE,
                $data['quantite']
            );
         
            // Les unités récupérées sont revenues en stock disponible
            $article = Article::find($affectation->article_id);
            if ($article?->is_archived) {
                $article->update(['is_archived' => false]);
            }

            return $recuperation;
        });
    }


    // RÉAFFECTER : salle change, stock inchangé
    // Seule la salle de destination change
    public function reaffecter(Affectation $affectation, array $data): Affectation
    {
        return DB::transaction(function () use ($affectation, $data) {
            if (!is_null($affectation->date_recuperation)) {
                throw new Exception("Cette affectation est déjà terminée.");
            }

            if ($data['salle_id'] == $affectation->salle_id
                && $data['bloc_id'] == $affectation->bloc_id) {
                throw new Exception(
                    "La destination doit être différente de la salle actuelle."
                );
            }

            if ($data['quantite'] > $affectation->quantite) {
                throw new Exception(
                    "Impossible de réaffecter {$data['quantite']} unité(s). " .
                    "L'affectation source concerne {$affectation->quantite} unité(s)."
                );
            }

            // Tracer la réaffectation
            Reaffectation::create([
                'affectation_id'     => $affectation->id,
                'salle_id'           => $data['salle_id'] ?? null,
                'quantite'           => $data['quantite'],
                'observations'       => $data['observations'] ?? null,
                'date_reaffectation' => now()->toDateString(),
            ]);

            // Clôturer partiellement ou totalement l'ancienne affectation
            $quantiteRestante = $affectation->quantite - $data['quantite'];

            if ($quantiteRestante > 0) {
                $affectation->update(['quantite' => $quantiteRestante]);
            } else {
                $affectation->update([
                    'date_recuperation' => now()->toDateString(),
                ]);
            }

            // Créer la nouvelle affectation vers la nouvelle salle
            return Affectation::create([
                'article_id'       => $affectation->article_id,
                'bloc_id'          => $data['bloc_id'],
                'salle_id'         => $data['salle_id'] ?? null,
                'quantite'         => $data['quantite'],
                'observations'     => $data['observations'] ?? null,
                'date_affectation' => now()->toDateString(),
                'user_id'          => Auth::id(),
            ]);
        });
    }

    // QUANTITÉ DISPONIBLE
    //
    // Ce qui change :
    // - Le nouveau code lit directement la ligne Disponible dans stocks
    //   C'est plus simple et plus fiable

    public function getQuantiteDisponible(Article $article): int
    {
        return Stock::quantitePour($article->id, Stock::DISPONIBLE);
    }
}