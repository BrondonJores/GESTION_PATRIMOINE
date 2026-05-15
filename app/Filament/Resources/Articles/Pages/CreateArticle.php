<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\ArticleService;
use Filament\Notifications\Notification;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    
    //validaton avant création
   protected function mutateFormDataBeforeCreate(array $data): array
{
    try {
        app(ArticleService::class)->valider($data);
    } catch (\Exception $e) {
        Notification::make()
            ->title('Création impossible')
            ->body($e->getMessage())
            ->danger()
            ->send();

        $this->halt(); // stoppe la sauvegarde proprement
    }

    return $data;
}
}
