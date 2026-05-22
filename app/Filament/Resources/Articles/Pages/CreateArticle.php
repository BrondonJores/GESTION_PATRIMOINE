<?php
// app/Filament/Resources/Articles/Pages/CreateArticle.php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Services\ArticleService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        try {
            app(ArticleService::class)->valider($data);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Création impossible')
                ->body($e->getMessage())
                ->danger()->send();
            $this->halt();
        }

        return $data;
    }
}