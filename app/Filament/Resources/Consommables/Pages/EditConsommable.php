<?php

namespace App\Filament\Resources\Consommables\Pages;

use App\Filament\Resources\Consommables\ConsommableResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditConsommable extends EditRecord
{
    protected static string $resource = ConsommableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->validerDonnees($data);

        // Recalculer le statut automatiquement à chaque modification
        $data['statut'] = $this->calculerStatut(
            (int) ($data['quantite_stock'] ?? 0),
            isset($data['quantite_min']) && $data['quantite_min'] !== ''
                ? (int) $data['quantite_min']
                : null
        );

        return $data;
    }

    private function validerDonnees(array $data): void
    {
        $quantite    = (int) ($data['quantite_stock'] ?? 0);
        $quantiteMin = isset($data['quantite_min']) && $data['quantite_min'] !== ''
                       ? (int) $data['quantite_min']
                       : null;

        if ($quantite < 0) {
            $this->notifierErreur("La quantité en stock ne peut pas être négative.");
        }

        if (!is_null($quantiteMin) && $quantiteMin < 0) {
            $this->notifierErreur("Le seuil minimal ne peut pas être négatif.");
        }

    }

    private function notifierErreur(string $message): never
    {
        Notification::make()
            ->title('Modification impossible')
            ->body($message)
            ->danger()
            ->persistent()
            ->send();

        $this->halt();

        throw new \RuntimeException($message);
    }

    private function calculerStatut(int $quantite, ?int $seuilMin): string
    {
        if ($quantite <= 0)                                    return 'Épuisé';
        if (!is_null($seuilMin) && $quantite <= $seuilMin)    return 'Sous seuil';
        return 'Disponible';
    }
}
