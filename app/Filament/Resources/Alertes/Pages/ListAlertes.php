<?php

namespace App\Filament\Resources\Alertes\Pages;

use App\Filament\Resources\Alertes\AlerteResource;
use Filament\Resources\Pages\ListRecords;

class ListAlertes extends ListRecords
{
    protected static string $resource = AlerteResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
