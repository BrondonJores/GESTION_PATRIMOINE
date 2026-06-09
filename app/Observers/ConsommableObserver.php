<?php

namespace App\Observers;

use App\Models\Alerte;
use App\Models\Consommable;
use App\Services\NotificationService;

class ConsommableObserver
{
    public function created(Consommable $consommable): void
    {
        $nouveauStatut = $consommable->calculerStatut();

        if ($consommable->statut !== $nouveauStatut) {
            $consommable->updateQuietly(['statut' => $nouveauStatut]);
        }

        // À la création on vérifie aussi les seuils
        $this->verifierSeuils($consommable);
    }

    public function updated(Consommable $consommable): void
    {
        // 1. On capture d'abord si le stock a changé, avant toute autre manipulation du modèle
        $stockAChange = $consommable->wasChanged('quantite_stock');
        $ancienStock  = (int) $consommable->getOriginal('quantite_stock');
        $nouveauStock = (int) $consommable->quantite_stock;

        // 2. Recalculer le statut automatiquement
        $nouveauStatut = $consommable->calculerStatut();

        if ($consommable->statut !== $nouveauStatut) {
            $consommable->updateQuietly(['statut' => $nouveauStatut]);
        }

        // 3. Vérifier les seuils uniquement si le stock a DIMINUÉ
        if ($stockAChange && $nouveauStock < $ancienStock) {
            $this->verifierSeuils($consommable);
        }
    }

    // LOGIQUE DES TROIS NIVEAUX
    // Niveau 1 — Seuil faible (30% au-dessus du seuil minimal)
    //   Condition : quantite_min < stock <= quantite_min * 1.3
    //   Action    : notification InApp uniquement — PAS d'alerte
    // Niveau 2 — Seuil minimal atteint
    //   Condition : 0 < stock <= quantite_min
    //   Action    : alerte créée (type stock_minimal_atteint)
    // Niveau 3 — Épuisé
    //   Condition : stock <= 0
    //   Action    : alerte créée (type stock_epuise)

    private function verifierSeuils(Consommable $consommable): void
    {
        if (is_null($consommable->quantite_min)) return;

        $stock    = (int) $consommable->quantite_stock;
        $seuilMin = (int) $consommable->quantite_min;

    
        $seuilFaible = (int) ceil($seuilMin * 1.3);

        if ($stock <= 0) {
            $this->creerAlerteSiAbsente(
                consommable : $consommable,
                typeAlerte  : 'stock_epuise',
                canal       : 'Tous',
                retour      : "Stock ÉPUISÉ : aucune unité disponible pour « {$consommable->designation} »."
            );

        } elseif ($stock <= $seuilMin) {
            $this->creerAlerteSiAbsente(
                consommable : $consommable,
                typeAlerte  : 'stock_minimal_atteint',
                canal       : 'Tous',
                retour      : "Stock MINIMAL atteint : {$stock} unité(s) disponible(s) pour « {$consommable->designation} ». Seuil : {$seuilMin}."
            );

        } elseif ($stock <= $seuilFaible) {
            $this->envoyerNotificationFaible($consommable, $stock, $seuilMin, $seuilFaible);
        }
    }

    // CRÉER UNE ALERTE (niveaux 2 et 3 uniquement)

    private function creerAlerteSiAbsente(
        Consommable $consommable,
        string $typeAlerte,
        string $canal,
        string $retour
    ): void {
        $dejaExistante = Alerte::where('consommable_id', $consommable->id)
                                ->where('statut', 'Non_traité')
                                ->exists();

        if ($dejaExistante) return;

        Alerte::create([
            'consommable_id' => $consommable->id,
            'type_alerte'    => $typeAlerte,
            'statut'         => 'Non_traité',
            'canal'          => $canal,
            'retour'         => $retour,
            'date_alerte'    => now(),
        ]);
    }

    // ENVOYER UNE NOTIFICATION  (niveau 1 uniquement)

    private function envoyerNotificationFaible(
        Consommable $consommable,
        int $stock,
        int $seuilMin,
        int $seuilFaible
    ): void {
        $contenu = "Stock FAIBLE pour « {$consommable->designation} » : "
                 . "{$stock} unité(s) disponible(s). "
                 . "Le seuil minimal ({$seuilMin}) sera bientôt atteint.";

        try {
            $service = app(\App\Services\NotificationService::class);
            $service->notifyUsers(
                $service->supportRecipients(),
                $contenu,
                'InApp' 
            );
        } catch (\Throwable) {
        }
    }
}