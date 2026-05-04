<?php

namespace App\Services;

use App\Models\Affectation;
use App\Models\Article;
use App\Models\Recuperation;
use App\Models\Reaffectation;
use Exception;
use Illuminate\Support\Facades\DB;

class AffectationService
{
    public function affecter(array $data): Affectation
    {
        return DB::transaction(function () use ($data) {
            $article = Article::findOrFail($data['article_id']);

            if ($article->statut === 'Réformé') {
                throw new Exception("Impossible d'affecter un article réformé.");
            }

            if ($article->statut === 'En_maintenance') {
                throw new Exception("Impossible d'affecter un article en maintenance.");
            }

            $quantiteDisponible = $this->getQuantiteDisponible($article);
            if ($data['quantite'] > $quantiteDisponible) {
                throw new Exception(
                    "Stock insuffisant. Disponible : {$quantiteDisponible}, demandé : {$data['quantite']}."
                );
            }

            $affectation = Affectation::create([
                'article_id'       => $article->id,
                'bloc_id'          => $data['bloc_id'],
                'salle_id'         => $data['salle_id'] ?? null,
                'quantite'         => $data['quantite'],
                'observations'     => $data['observations'] ?? null,
                'date_affectation' => $data['date_affectation'] ?? now()->toDateString(),
                'user_id'          => auth()->id(),
            ]);

            $nouvelleQuantite = $article->quantite - $data['quantite'];
            $article->update([
                'quantite' => $nouvelleQuantite,
                'statut'   => $nouvelleQuantite <= 0 ? 'Affecté' : 'Disponible',
            ]);

            return $affectation;
        });
    }

   public function recuperer(Affectation $affectation, array $data): Recuperation
{
    return DB::transaction(function () use ($affectation, $data) {
        if (!is_null($affectation->date_recuperation)) {
            throw new Exception("Cette affectation a déjà été récupérée.");
        }

        if ($data['quantite'] > $affectation->quantite) {
            throw new Exception(
                "Impossible de récupérer {$data['quantite']} unité(s). " .
                "L'affectation concerne seulement {$affectation->quantite} unité(s)."
            );
        }

        $recuperation = Recuperation::create([
            'affectation_id'    => $affectation->id,
            'quantite'          => $data['quantite'],
            'observations'      => $data['observations'] ?? null,
            'date_recuperation' => $data['date_recuperation'] ?? now()->toDateString(),
        ]);

        $quantiteRestante = $affectation->quantite - $data['quantite'];

        if ($quantiteRestante > 0) {
            // Récupération partielle → on réduit la quantité, affectation reste Active
            $affectation->update([
                'quantite' => $quantiteRestante,
            ]);
        } else {
            // Récupération totale → on clôture l'affectation
            $affectation->update([
                'date_recuperation' => $data['date_recuperation'] ?? now()->toDateString(),
            ]);
        }

        // Remettre la quantité récupérée dans le stock de l'article
        $article = $affectation->article;
        $article->update([
            'quantite' => $article->quantite + $data['quantite'],
            'statut'   => 'Disponible',
        ]);

        return $recuperation;
    });
}
   public function reaffecter(Affectation $affectation, array $data): Affectation
{
    return DB::transaction(function () use ($affectation, $data) {
        if (!is_null($affectation->date_recuperation)) {
            throw new Exception("Cette affectation est déjà terminée.");
        }

        if ($data['salle_id'] == $affectation->salle_id && $data['bloc_id'] == $affectation->bloc_id) {
            throw new Exception("Le bloc et la salle doivent être différents de l'affectation actuelle.");
        }

        if ($data['quantite'] > $affectation->quantite) {
            throw new Exception(
                "Impossible de réaffecter {$data['quantite']} unité(s). " .
                "L'affectation source concerne {$affectation->quantite} unité(s)."
            );
        }

        Reaffectation::create([
            'affectation_id'     => $affectation->id,
            'salle_id'           => $data['salle_id'] ?? null,
            'quantite'           => $data['quantite'],
            'observations'       => $data['observations'] ?? null,
            'date_reaffectation' => now()->toDateString(),
        ]);

        $quantiteRestante = $affectation->quantite - $data['quantite'];

        if ($quantiteRestante > 0) {
            // Réaffectation partielle → on réduit la quantité de l'affectation source
            $affectation->update([
                'quantite' => $quantiteRestante,
            ]);
        } else {
            // Réaffectation totale → on clôture l'affectation source
            $affectation->update([
                'date_recuperation' => now()->toDateString(),
            ]);
        }

        // Créer la nouvelle affectation vers le nouveau bloc/salle
        return Affectation::create([
            'article_id'       => $affectation->article_id,
            'bloc_id'          => $data['bloc_id'],
            'salle_id'         => $data['salle_id'] ?? null,
            'quantite'         => $data['quantite'],
            'observations'     => $data['observations'] ?? null,
            'date_affectation' => now()->toDateString(),
            'user_id'          => auth()->id(),
        ]);
    });
}
    public function getQuantiteDisponible(Article $article): int
    {
        $affectee = Affectation::where('article_id', $article->id)
            ->whereNull('date_recuperation')
            ->sum('quantite');

        return max(0, $article->quantite - $affectee);
    }
}