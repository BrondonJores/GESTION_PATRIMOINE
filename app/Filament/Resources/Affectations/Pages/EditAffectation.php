<?php

namespace App\Filament\Resources\Affectations\Pages;

use App\Filament\Resources\Affectations\AffectationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAffectation extends EditRecord
{
    protected static string $resource = AffectationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}