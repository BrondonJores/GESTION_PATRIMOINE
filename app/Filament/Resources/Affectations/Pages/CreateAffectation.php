<?php
// app/Filament/Resources/Affectations/Pages/CreateAffectation.php

namespace App\Filament\Resources\Affectations\Pages;

use App\Filament\Resources\Affectations\AffectationResource;
use App\Services\AffectationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAffectation extends CreateRecord
{
    protected static string $resource = AffectationResource::class;

    // Intercepter la création pour router vers la bonne méthode du service
    protected function handleRecordCreation(array $data): Model
    {
        try {
            if ($data['type'] === 'article') {
                return app(AffectationService::class)->affecterArticle($data);
            } else {
                return app(AffectationService::class)->affecterConsommable($data);
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Affectation impossible')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            $this->halt();

            // Jamais atteint — halt() arrête l'exécution
            return new \App\Models\Affectation();
        }
    }
}