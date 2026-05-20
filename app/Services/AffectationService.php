<?php
// app/Services/AffectationService.php

namespace App\Services;

use App\Models\Affectation;
use App\Models\Article;
use App\Models\Consommable;
use App\Models\Reaffectation;
use App\Models\Recuperation;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AffectationService
{
    // ══════════════════════════════════════════════════════════════
    // AFFECTER UN ARTICLE NON CONSOMMABLE
    // type = article, quantite = 1
    // Disponible → Affecté sur article.statut
    // ══════════════════════════════════════════════════════════════

    public function affecterArticle(array $data): Affectation
    {
        return DB::transaction(function () use ($data) {
            $article = Article::findOrFail($data['article_id']);

            if (!$article->estDisponible()) {
                throw new Exception(
                    "Cet article ne peut pas être affecté. " .
                    "Statut actuel : {$article->statut}."
                );
            }

            $affectation = Affectation::create([
                'type'             => 'article',
                'article_id'       => $article->id,
                'consommable_id'   => null,
                'bloc_id'          => $data['bloc_id'],
                'salle_id'         => $data['salle_id'] ?? null,
                'quantite'         => 1, // toujours 1 — unité physique unique
                'date_affectation' => $data['date_affectation'] ?? now()->toDateString(),
                'observations'     => $data['observations'] ?? null,
                'user_id'          => Auth::id(),
            ]);

            // Changer le statut de l'article
            $article->update(['statut' => Article::AFFECTE]);

            return $affectation;
        });
    }

    // ══════════════════════════════════════════════════════════════
    // AFFECTER UN CONSOMMABLE
    // type = consommable, quantite = X
    // Diminue consommable.quantite_stock définitivement
    // ══════════════════════════════════════════════════════════════

    public function affecterConsommable(array $data): Affectation
    {
        return DB::transaction(function () use ($data) {
            $consommable = Consommable::findOrFail($data['consommable_id']);

            if ($consommable->quantite_stock <= 0) {
                throw new Exception(
                    "Stock épuisé — impossible d'affecter {$consommable->designation}."
                );
            }

            if ($data['quantite'] > $consommable->quantite_stock) {
                throw new Exception(
                    "Stock insuffisant. Disponible : {$consommable->quantite_stock}, " .
                    "demandé : {$data['quantite']}."
                );
            }

            $affectation = Affectation::create([
                'type'             => 'consommable',
                'article_id'       => null,
                'consommable_id'   => $consommable->id,
                'bloc_id'          => $data['bloc_id'],
                'salle_id'         => $data['salle_id'] ?? null,
                'quantite'         => $data['quantite'],
                'date_affectation' => $data['date_affectation'] ?? now()->toDateString(),
                'date_recuperation'=> now()->toDateString(), // immédiatement clôturée
                // Un consommable affecté = consommé, pas de récupération
                'observations'     => $data['observations'] ?? null,
                'user_id'          => Auth::id(),
            ]);

            // Diminuer le stock — ConsommableObserver crée l'alerte si besoin
            $consommable->update([
                'quantite_stock' => $consommable->quantite_stock - $data['quantite'],
            ]);

            return $affectation;
        });
    }

    // ══════════════════════════════════════════════════════════════
    // RÉCUPÉRER UN ARTICLE
    // Affecté → Disponible
    // Uniquement pour type = article
    // ══════════════════════════════════════════════════════════════

    public function recuperer(Affectation $affectation, array $data): Recuperation
    {
        return DB::transaction(function () use ($affectation, $data) {
            if ($affectation->estPourConsommable()) {
                throw new Exception(
                    "Un consommable affecté ne peut pas être récupéré."
                );
            }

            if (!$affectation->estActive()) {
                throw new Exception("Cette affectation a déjà été clôturée.");
            }

            $article = $affectation->article;

            $recuperation = Recuperation::create([
                'affectation_id'    => $affectation->id,
                'quantite'          => 1,
                'observations'      => $data['observations'] ?? null,
                'date_recuperation' => $data['date_recuperation'] ?? now()->toDateString(),
            ]);

            $affectation->update([
                'date_recuperation' => $data['date_recuperation'] ?? now()->toDateString(),
            ]);

            // Remettre l'article disponible
            $article->update(['statut' => Article::DISPONIBLE]);

            return $recuperation;
        });
    }

    // ══════════════════════════════════════════════════════════════
    // RÉAFFECTER UN ARTICLE
    // Affecté → Affecté dans une autre salle
    // Statut article reste Affecté — seule la salle change
    // ══════════════════════════════════════════════════════════════

    public function reaffecter(Affectation $affectation, array $data): Affectation
    {
        return DB::transaction(function () use ($affectation, $data) {
            if ($affectation->estPourConsommable()) {
                throw new Exception("Un consommable ne peut pas être réaffecté.");
            }

            if (!$affectation->estActive()) {
                throw new Exception("Cette affectation est déjà terminée.");
            }

            // Tracer la réaffectation
            Reaffectation::create([
                'affectation_id'     => $affectation->id,
                'salle_id'           => $data['salle_id'] ?? null,
                'quantite'           => 1,
                'observations'       => $data['observations'] ?? null,
                'date_reaffectation' => now()->toDateString(),
            ]);

            // Clôturer l'ancienne
            $affectation->update([
                'date_recuperation' => now()->toDateString(),
            ]);

            // Créer la nouvelle — statut article reste Affecté
            return Affectation::create([
                'type'             => 'article',
                'article_id'       => $affectation->article_id,
                'consommable_id'   => null,
                'bloc_id'          => $data['bloc_id'],
                'salle_id'         => $data['salle_id'] ?? null,
                'quantite'         => 1,
                'date_affectation' => now()->toDateString(),
                'observations'     => $data['observations'] ?? null,
                'user_id'          => Auth::id(),
            ]);
        });
    }

    // ══════════════════════════════════════════════════════════════
    // RÉAPPROVISIONNER UN CONSOMMABLE
    // Augmente quantite_stock
    // ══════════════════════════════════════════════════════════════

    public function reapprovisionner(
        Consommable $consommable,
        int $quantite,
        string $motif
    ): void {
        DB::transaction(function () use ($consommable, $quantite, $motif) {
            if ($quantite <= 0) {
                throw new Exception("La quantité doit être supérieure à zéro.");
            }

            $consommable->update([
                'quantite_stock' => $consommable->quantite_stock + $quantite,
                'observations'   => trim(
                    ($consommable->observations ?? '') .
                    "\n[RÉAPPROVISIONNEMENT +{$quantite}] {$motif}"
                ),
            ]);
        });
    }
}