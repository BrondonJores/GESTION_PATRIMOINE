<?php

namespace App\Filament\Resources\Blocs\Pages;

use App\Filament\Resources\Blocs\BlocResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBloc extends EditRecord
{
    protected static string $resource = BlocResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}