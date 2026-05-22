<?php

namespace App\Filament\Resources\Familles\Pages;

use App\Filament\Resources\Familles\FamilleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFamille extends EditRecord
{
    protected static string $resource = FamilleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
