<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\ArticleService;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;

    
    //validaton avant création
     protected function mutateFormDataBeforeCreate(array $data): array
    {
        app(ArticleService::class)->valider($data);
        return $data;
    }
}
