<?php

namespace App\Filament\Resources\Alertes\Pages;

use App\Filament\Resources\Alertes\AlerteResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAlerte extends ViewRecord
{
    protected static string $resource = AlerteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
