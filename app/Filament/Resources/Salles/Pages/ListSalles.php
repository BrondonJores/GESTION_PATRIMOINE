<?php

namespace App\Filament\Resources\Salles\Pages;

use App\Filament\Resources\Salles\SalleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalles extends ListRecords
{
    protected static string $resource = SalleResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}