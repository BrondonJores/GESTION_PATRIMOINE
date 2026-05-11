<?php

namespace App\Filament\Resources\Rapports\Pages;

use App\Filament\Resources\Rapports\RapportResource;
use App\Services\Reports\ReportRowsBuilder;
use App\Services\RapportService;
use Illuminate\Database\Eloquent\Model;
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

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(RapportService::class);
        $lignes = app(ReportRowsBuilder::class)->build($data);
        $periode = [
            'debut' => $data['periode_debut'],
            'fin' => $data['periode_fin'],
        ];

        return $data['format'] === 'Excel'
            ? $service->exportExcel($data['type_rapport'], $lignes, auth()->user(), $periode)
            : $service->exportPdf($data['type_rapport'], $lignes, auth()->user(), $periode);
    }
}
