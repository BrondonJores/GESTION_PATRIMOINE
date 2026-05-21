<?php

namespace App\Filament\Resources\Affectations\Pages;

use App\Filament\Resources\Affectations\AffectationResource;
use App\Services\AffectationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAffectation extends EditRecord
{
    protected static string $resource = AffectationResource::class;

    protected function getHeaderActions(): array
    {
        $affectation = $this->record;
        $service = new AffectationService();
        $actions = [];

        // Bouton Récupérer — seulement pour articles actifs
        if ($affectation->type === 'article' && $affectation->estActive()) {
            $actions[] = Action::make('recuperer')
                ->label('Récupérer')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Récupérer cet article')
                ->modalDescription('L\'article sera remis en stock et marqué Disponible.')
                ->modalSubmitActionLabel('Confirmer la récupération')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('date_recuperation')
                        ->label('Date de récupération')
                        ->default(now())
                        ->required(),
                    \Filament\Forms\Components\Textarea::make('observations')
                        ->label('Observations'),
                ])
                ->action(function (array $data) use ($affectation, $service) {
                    $service->recuperer($affectation, $data);
                    Notification::make()
                        ->title('Article récupéré avec succès')
                        ->success()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                });
        }

        // Bouton Réaffecter — seulement pour articles actifs
        if ($affectation->type === 'article' && $affectation->estActive()) {
            $actions[] = Action::make('reaffecter')
                ->label('Réaffecter')
                ->icon('heroicon-o-arrows-right-left')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Réaffecter cet article')
                ->modalDescription('L\'article sera déplacé vers un autre bloc/salle.')
                ->modalSubmitActionLabel('Confirmer la réaffectation')
                ->form([
                    \Filament\Forms\Components\Select::make('bloc_id')
                        ->label('Nouveau Bloc')
                        ->required()
                        ->options(\App\Models\Bloc::where('actif', true)->pluck('nom_bloc', 'id'))
                        ->searchable()
                        ->live(),
                    \Filament\Forms\Components\Select::make('salle_id')
                        ->label('Nouvelle Salle (optionnelle)')
                        ->options(fn(\Filament\Forms\Get $get) =>
                            $get('bloc_id')
                                ? \App\Models\Salle::where('bloc_id', $get('bloc_id'))
                                    ->where('actif', true)->pluck('nom_salle', 'id')
                                : [])
                        ->searchable()
                        ->placeholder('-- Tout le bloc --'),
                    \Filament\Forms\Components\Textarea::make('observations')
                        ->label('Observations'),
                ])
                ->action(function (array $data) use ($affectation, $service) {
                    $service->reaffecter($affectation, $data);
                    Notification::make()
                        ->title('Article réaffecté avec succès')
                        ->success()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                });
        }

        $actions[] = DeleteAction::make()
            ->requiresConfirmation()
            ->modalHeading('Supprimer l\'affectation')
            ->modalDescription('Cette action est irréversible.')
            ->modalSubmitActionLabel('Oui, supprimer');

        return $actions;
    }
}