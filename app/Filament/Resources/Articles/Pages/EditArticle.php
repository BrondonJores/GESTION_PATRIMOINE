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

    protected function getHeaderActions(): array
    {
        return [
            //suppression personnalisée avec validation métier
            DeleteAction::make()
             ->action(function () {
                    app(ArticleService::class)->supprimer($this->getRecord());
                    $this->redirect($this->getResource()::getUrl('index'));
                })
        ];
    }

    //validation avant modification
     protected function mutateFormDataBeforeSave(array $data): array
    {
        try {
            app(ArticleService::class)->valider($data, $this->getRecord());

            if (isset($data['statut']) && $data['statut'] === 'Réformé') {
                $data['etat'] = 'Réformé';
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }

        return $data;
    }
    }
