<?php
// app/Services/ArticleService.php

namespace App\Services;

use App\Models\Article;
use Exception;

class ArticleService
{
    public function valider(array $data, ?Article $article = null): void
    {
        // Un article réformé est intouchable sauf par l'admin
        if ($article?->estReforme()) {
            if (!auth()->user()?->hasRole('admin')) {
                throw new Exception(
                    "Un article réformé ne peut pas être modifié. " .
                    "Contactez l'administrateur."
                );
            }
        }
    }

    // Disponible → En_maintenance
    public function mettreEnMaintenance(Article $article, string $motif): void
    {
        if (!$article->estDisponible()) {
            throw new Exception(
                "Seul un article disponible peut être mis en maintenance. " .
                "Statut actuel : {$article->statut}."
            );
        }

        $article->update([
            'statut'       => Article::MAINTENANCE,
            'observations' => "[MAINTENANCE — " . now()->format('d/m/Y') . "] " . $motif,
        ]);
    }

    // En_maintenance → Disponible
    public function retourMaintenance(Article $article): void
    {
        if (!$article->estEnMaintenance()) {
            throw new Exception("Cet article n'est pas en maintenance.");
        }

        $article->update(['statut' => Article::DISPONIBLE,
         'observations' => "[RETOUR MAINTENANCE — " . now()->format('d/m/Y') . "]",
        ]);
    }

    // Disponible ou En_maintenance → Réformé (irréversible)
    public function reformer(Article $article, string $motif): void
    {
        if ($article->estAffecte()) {
            throw new Exception(
                "Impossible de réformer un article affecté. Récupérez-le d'abord."
            );
        }

        if ($article->estReforme()) {
            throw new Exception("Cet article est déjà réformé.");
        }

        $article->update([
            'statut'       => Article::REFORME,
              'observations' => "[RÉFORME — " . now()->format('d/m/Y') . "] " . $motif,
    
        ]);
    }

    // RÉINTÉGRER
    // Réformé → Disponible
    // Réservé exclusivement à l'administrateur
    // Cas d'usage : erreur de réforme, article réparé externement
    public function reintegrer(Article $article, string $motif): void
    {
        // Sécurité : uniquement l'admin peut réintégrer
        if (!auth()->user()?->hasRole('admin')) {
            throw new Exception(
                "Action réservée à l'administrateur."
            );
        }

        // L'article doit être réformé pour pouvoir être réintégré
        if (!$article->estReforme()) {
            throw new Exception(
                "Seul un article réformé peut être réintégré. " .
                "Statut actuel : {$article->statut}."
            );
        }

        $article->update([
            'statut'       => Article::DISPONIBLE,
          'observations' => "[RÉINTÉGRATION — " . now()->format('d/m/Y') . "] " . $motif,
        ]);
    }

    public function generateQrCode(Article $article): string
    {
        return (new \chillerlan\QRCode\QRCode)->render($article->numero_reference);
    }

    // Statistiques — calcul direct sur article.statut
    public function getStatistiques(): array
    {
        return [
            'total'          => Article::whereNotIn('statut', [Article::REFORME])->count(),
            'disponibles'    => Article::where('statut', Article::DISPONIBLE)->count(),
            'affectes'       => Article::where('statut', Article::AFFECTE)->count(),
            'en_maintenance' => Article::where('statut', Article::MAINTENANCE)->count(),
            'reformes'       => Article::where('statut', Article::REFORME)->count(),
        ];
    }
}