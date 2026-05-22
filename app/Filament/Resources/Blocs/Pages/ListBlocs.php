<?php

namespace App\Filament\Resources\Blocs\Pages;

use App\Filament\Resources\Blocs\BlocResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBlocs extends ListRecords
{
    protected static string $resource = BlocResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}