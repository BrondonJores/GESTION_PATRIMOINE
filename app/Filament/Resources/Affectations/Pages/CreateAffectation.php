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
    $service = new AffectationService();

    if ($data['type'] === 'consommable') {
        return $service->affecterConsommable($data);
    }

    // Affecter plusieurs articles dans le même bloc/salle
    $articleIds = $data['article_ids'];
    $lastAffectation = null;

    foreach ($articleIds as $articleId) {
        $lastAffectation = $service->affecterArticle(array_merge($data, [
            'article_id' => $articleId,
        ]));
    }

    return $lastAffectation;
}    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}