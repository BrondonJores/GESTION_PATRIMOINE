<?php

namespace App\Filament\Resources\Affectations\Pages;

use App\Filament\Resources\Affectations\AffectationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAffectations extends ListRecords
{
    protected static string $resource = AffectationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}