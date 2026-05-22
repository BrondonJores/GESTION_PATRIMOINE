<?php

namespace App\Filament\Resources\Familles\Pages;

use App\Filament\Resources\Familles\FamilleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFamilles extends ListRecords
{
    protected static string $resource = FamilleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
