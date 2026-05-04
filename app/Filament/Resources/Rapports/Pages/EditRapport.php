<?php

namespace App\Filament\Resources\Rapports\Pages;

use App\Filament\Resources\Rapports\RapportResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRapport extends EditRecord
{
    protected static string $resource = RapportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
