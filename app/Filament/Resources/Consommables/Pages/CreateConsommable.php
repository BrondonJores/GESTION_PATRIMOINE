<?php

namespace App\Filament\Resources\Consommables\Pages;

use App\Filament\Resources\Consommables\ConsommableResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;


class CreateConsommable extends CreateRecord
{
    protected static string $resource = ConsommableResource::class;


     protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->validerDonnees($data);

        // Calculer le statut automatiquement avant insertion
        // Ne pas laisser l'utilisateur le choisir manuellement
        $data['statut'] = $this->calculerStatut(
            (int) ($data['quantite_stock'] ?? 0),
            isset($data['quantite_min']) ? (int) $data['quantite_min'] : null
        );

        return $data;
    }

    // Validation des règles métier
    private function validerDonnees(array $data): void
    {
        $quantite    = (int) ($data['quantite_stock'] ?? 0);
        $quantiteMin = isset($data['quantite_min']) && $data['quantite_min'] !== ''
                       ? (int) $data['quantite_min']
                       : null;

        // Règle 1 : quantité ne peut pas être négative
        if ($quantite < 0) {
            $this->notifierErreur("La quantité en stock ne peut pas être négative.");
        }

        // Règle 2 : seuil minimal ne peut pas être négatif
        if (!is_null($quantiteMin) && $quantiteMin < 0) {
            $this->notifierErreur("Le seuil minimal ne peut pas être négatif.");
        }

        // Règle 3 : quantité ne peut pas être inférieure au seuil
        if (!is_null($quantiteMin) && $quantite < $quantiteMin) {
            $this->notifierErreur(
                "La quantité en stock ({$quantite}) ne peut pas être " .
                "inférieure au seuil minimal ({$quantiteMin})."
            );
        }

    }

    private function notifierErreur(string $message): never
    {
        Notification::make()
            ->title('Validation impossible')
            ->body($message)
            ->danger()
            ->persistent()
            ->send();

        $this->halt();

        // jamais atteint — halt() stoppe l'exécution
        throw new \RuntimeException($message);
    }

    // Calcul automatique du statut selon la logique métier
    private function calculerStatut(int $quantite, ?int $seuilMin): string
    {
        if ($quantite <= 0)                                    return 'Épuisé';
        if (!is_null($seuilMin) && $quantite <= $seuilMin)    return 'Sous seuil';
        return 'Disponible';
    }
}
