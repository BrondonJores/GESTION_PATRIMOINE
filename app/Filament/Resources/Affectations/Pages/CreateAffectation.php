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

   protected function handleRecordCreation(array $data): Model
{
    $service = new AffectationService();

    try {
        if ($data['type'] === 'consommable') {
            return $service->affecterConsommable($data);
        }

        $articleIds = $data['article_ids'] ?? [];

        if (empty($articleIds)) {
            throw new \Exception("Aucun article sélectionné.");
        }

        $lastAffectation = null;

        foreach ($articleIds as $articleId) {
            $lastAffectation = $service->affecterArticle(
                array_merge($data, ['article_id' => $articleId])
            );
        }

        return $lastAffectation;

    } catch (\Exception $e) {
        Notification::make()
            ->title('Affectation impossible')
            ->body($e->getMessage())
            ->danger()
            ->persistent()
            ->send();

        $this->halt();

        return new \App\Models\Affectation();
    }
}

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}