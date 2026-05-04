<?php

namespace App\Filament\Resources\Affectations\Pages;

use App\Filament\Resources\Affectations\AffectationResource;
use App\Services\AffectationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAffectation extends CreateRecord
{
    protected static string $resource = AffectationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(AffectationService::class)->affecter($data);
    }
}