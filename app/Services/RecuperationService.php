<?php

namespace App\Services;

use App\Models\Affectation;
use App\Models\Recuperation;
use App\Models\Article;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RecuperationService
{
    /**
     * Récupérer un article depuis une affectation
     */
    public function recuperer(Affectation $affectation, int $quantite, ?string $observations = null): Recuperation
    {
        // Validations
        if ($quantite <= 0) {
            throw new \InvalidArgumentException("La quantité doit être supérieure à 0.");
        }

        if ($affectation->quantite < $quantite) {
            throw new \Exception("Quantité insuffisante dans cette affectation. Disponible : {$affectation->quantite}");
        }

        return DB::transaction(function () use ($affectation, $quantite, $observations) {
            $article = $affectation->article;

            // Créer la récupération
            $recuperation = Recuperation::create([
                'affectation_id'    => $affectation->id,
                'quantite'          => $quantite,
                'observations'      => $observations,
                'date_recuperation' => now(),
            ]);

            // Remettre en stock
            $article->increment('quantite', $quantite);

            // Mettre à jour le statut
            $article->update(['statut' => 'Disponible']);

            // Mettre à jour la quantité dans l'affectation
            $affectation->decrement('quantite', $quantite);

            // Supprimer l'affectation si quantite = 0
            if ($affectation->fresh()->quantite === 0) {
                $affectation->delete();
            }

            // Log d'audit
            AuditLog::create([
                'module'      => 'Affectations',
                'action'      => 'Récupération',
                'adresse_ip'  => request()->ip(),
                'user_id'     => Auth::id(),
                'date_action' => now(),
            ]);

            Log::info("Récupération créée", [
                'affectation_id' => $affectation->id,
                'article_id'     => $article->id,
                'quantite'       => $quantite,
                'user_id'        => Auth::id(),
            ]);

            return $recuperation;
        });
    }
}