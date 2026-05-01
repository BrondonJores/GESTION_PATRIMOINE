<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Services\ArticleService;

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
        app(ArticleService::class)->valider($data, $this->getRecord());
         // Si statut = Réformé → forcer etat = Réformé directement dans $data
        // Comme ça Filament sauvegarde les deux champs en même temps
        if (isset($data['statut']) && $data['statut'] === 'Réformé') {
            $data['etat'] = 'Réformé';
        }
        if (isset($data['etat'])&& $data['etat']=== 'Réformé'){
            $data['statut']= 'Réformé';
        }

        return $data;
    }
}
