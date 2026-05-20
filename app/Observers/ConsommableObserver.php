<?php
// app/Observers/ConsommableObserver.php

namespace App\Observers;

use App\Models\Alerte;
use App\Models\Consommable;

class ConsommableObserver
{
    public function updated(Consommable $consommable): void
    {
        // Recalculer le statut après chaque modification
        $nouveauStatut = $consommable->calculerStatut();

        if ($consommable->statut !== $nouveauStatut) {
            // updateQuietly évite la boucle infinie updated → update → updated
            $consommable->updateQuietly(['statut' => $nouveauStatut]);
        }

        // Créer une alerte uniquement si le stock a diminué
        if ($consommable->wasChanged('quantite_stock')) {
            $ancien  = (int) $consommable->getOriginal('quantite_stock');
            $nouveau = (int) $consommable->quantite_stock;

            // Stock monte (réapprovisionnement) → pas d'alerte
            if ($nouveau >= $ancien) return;

            $this->verifierAlerte($consommable);
        }
    }

    private function verifierAlerte(Consommable $consommable): void
    {
        if (is_null($consommable->quantite_min)) return;

        $stock       = $consommable->quantite_stock;
        $seuilMin    = $consommable->quantite_min;
        $seuilFaible = $seuilMin * 2;

        if ($stock <= 0) {
            $canal  = 'Tous';
            $retour = "Consommable ÉPUISÉ : {$consommable->designation}.";
        } elseif ($stock <= $seuilMin) {
            $canal  = 'Tous';
            $retour = "Stock MINIMAL : {$stock} unité(s) de {$consommable->designation}. Seuil : {$seuilMin}.";
        } elseif ($stock <= $seuilFaible) {
            $canal  = 'InApp';
            $retour = "Stock FAIBLE : {$stock} unité(s) de {$consommable->designation}.";
        } else {
            return;
        }

        // Anti-doublon
        $existe = Alerte::where('consommable_id', $consommable->id)
                        ->where('statut', 'Non_traité')
                        ->exists();
        if ($existe) return;

        Alerte::create([
            'consommable_id' => $consommable->id,
            'article_id'     => null,
            'statut'         => 'Non_traité',
            'canal'          => $canal,
            'retour'         => $retour,
            'date_alerte'    => now(),
        ]);

    }
}