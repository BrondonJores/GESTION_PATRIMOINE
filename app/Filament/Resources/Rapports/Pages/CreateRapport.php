<?php

namespace App\Filament\Resources\Rapports\Pages;

use App\Filament\Resources\Rapports\RapportResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRapport extends CreateRecord
{
    protected static string $resource = RapportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['date_generation'] = now();
        $data['chemin_fichier'] = null;

        return $data;
    }
}
