<?php

namespace App\Filament\Resources\Salles\Pages;

use App\Filament\Resources\Salles\SalleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSalle extends EditRecord
{
    protected static string $resource = SalleResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}