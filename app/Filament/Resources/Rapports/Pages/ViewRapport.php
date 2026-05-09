<?php

namespace App\Filament\Resources\Rapports\Pages;

use App\Filament\Resources\Rapports\RapportResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ViewRapport extends ViewRecord
{
    protected static string $resource = RapportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('telecharger')
                ->label('Télécharger')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('success')
                ->visible(fn (): bool => RapportResource::canDownload($this->getRecord()))
                ->action(fn (): mixed => Storage::disk('local')->download(
                    $this->getRecord()->chemin_fichier,
                    RapportResource::downloadName($this->getRecord()),
                )),
            EditAction::make(),
        ];
    }
}
