<?php

namespace App\Filament\Resources\Alertes\Pages;

use App\Filament\Resources\Alertes\AlerteResource;
use App\Models\Alerte;
use App\Services\AlerteStatusService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewAlerte extends ViewRecord
{
    protected static string $resource = AlerteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('prendre_en_charge')
                ->label('Prendre en charge')
                ->icon(Heroicon::OutlinedClock)
                ->color('warning')
                ->visible(fn (): bool => $this->getRecord()->statut === 'Non_traité')
                ->action(fn (): Alerte => app(AlerteStatusService::class)->prendreEnCharge($this->getRecord())),
            Action::make('marquer_resolue')
                ->label('Marquer résolue')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->schema([
                    Textarea::make('note_resolution')
                        ->label('Note de résolution')
                        ->required()
                        ->maxLength(1000),
                ])
                ->visible(fn (): bool => $this->getRecord()->statut !== 'Résolu')
                ->action(fn (array $data): Alerte => app(AlerteStatusService::class)->marquerResolue(
                    $this->getRecord(),
                    $data['note_resolution'] ?? null,
                )),
        ];
    }
}
