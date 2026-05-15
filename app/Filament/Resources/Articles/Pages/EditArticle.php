<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Services\ArticleService;
use Filament\Notifications\Notification;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    // Stocker l'ancienne quantite_totale avant modification
    // pour calculer le delta dans afterSave()
    private int $ancienneQuantiteTotale = 0;

    protected function getHeaderActions(): array
    {
        return [];
    }

    //validation avant modification
      protected function mutateFormDataBeforeSave(array $data): array
    {
        try {
            app(ArticleService::class)->valider($data, $this->getRecord());
        } catch (\Exception $e) {
            Notification::make()
                ->title('Modification impossible')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Filament a sauvegardé quantite_totale en base.
        // On recalcule le Disponible depuis la vérité absolue :
        // disponible = total - affecté - maintenance - réformé
        try {
            app(ArticleService::class)
                ->synchroniserStockDisponible($this->getRecord());
        } catch (\Exception $e) {
            Notification::make()
                ->title('Avertissement stock')
                ->body($e->getMessage())
                ->warning()
                ->persistent()
                ->send();
        }
    }
    }
