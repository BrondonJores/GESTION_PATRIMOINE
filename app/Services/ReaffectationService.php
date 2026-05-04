<?php

namespace App\Services;

use App\Models\Affectation;
use App\Models\Reaffectation;
use App\Models\Salle;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReaffectationService
{
    /**
     * Réaffecter un article vers une autre salle
     */
    public function reaffecter(Affectation $affectation, Salle $nouvelleSalle, int $quantite, ?string $observations = null): Reaffectation
    {
        // Validations
        if ($quantite <= 0) {
            throw new \InvalidArgumentException("La quantité doit être supérieure à 0.");
        }

        if ($affectation->quantite < $quantite) {
            throw new \Exception("Quantité insuffisante dans cette affectation. Disponible : {$affectation->quantite}");
        }

        if (!$nouvelleSalle->actif) {
            throw new \Exception("La salle de destination est inactive.");
        }

        if ($affectation->salle_id === $nouvelleSalle->id) {
            throw new \Exception("La salle de destination est la même que la salle actuelle.");
        }

        return DB::transaction(function () use ($affectation, $nouvelleSalle, $quantite, $observations) {
            // Créer la réaffectation
            $reaffectation = Reaffectation::create([
                'affectation_id'    => $affectation->id,
                'quantite'          => $quantite,
                'observations'      => $observations,
                'date_reaffectation' => now(),
            ]);

            // Mettre à jour la salle dans l'affectation
            $affectation->update([
                'salle_id' => $nouvelleSalle->id,
            ]);

            // Log d'audit
            AuditLog::create([
                'module'      => 'Affectations',
                'action'      => 'Réaffectation',
                'adresse_ip'  => request()->ip(),
                'user_id'     => Auth::id(),
                'date_action' => now(),
            ]);

            Log::info("Réaffectation créée", [
                'affectation_id'    => $affectation->id,
                'nouvelle_salle_id' => $nouvelleSalle->id,
                'quantite'          => $quantite,
                'user_id'           => Auth::id(),
            ]);

            return $reaffectation;
        });
    }
}