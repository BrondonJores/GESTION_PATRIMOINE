<?php

namespace App\Filament\Resources\Alertes\Schemas;

use App\Support\Alertes\StockAlertType;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class AlerteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Alerte')
                    ->icon(Heroicon::OutlinedExclamationTriangle)
                    ->description("Résumé opérationnel de l'alerte et de sa priorité.")
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                    ])
                    ->schema([
                        TextEntry::make('consommable.designation')
                            ->label('Consommable concerné')
                            ->icon(Heroicon::OutlinedCube)
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold)
                            ->placeholder('Consommable indisponible')
                            ->columnSpanFull(),
                        TextEntry::make('statut')
                            ->label('Statut')
                            ->badge()
                            ->icon(fn (?string $state): Heroicon => match ($state) {
                                'Résolu' => Heroicon::OutlinedCheckCircle,
                                'En_cours' => Heroicon::OutlinedClock,
                                default => Heroicon::OutlinedExclamationCircle,
                            })
                            ->color(fn (?string $state): string => match ($state) {
                                'Résolu' => 'success',
                                'En_cours' => 'warning',
                                'Non_traité' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('type_alerte')
                            ->label("Type d'alerte")
                            ->formatStateUsing(fn (?string $state): string => StockAlertType::label($state))
                            ->badge()
                            ->icon(Heroicon::OutlinedTag)
                            ->color(fn (?string $state): string => match ($state) {
                                StockAlertType::STOCK_EPUISE => 'danger',
                                StockAlertType::STOCK_MINIMAL => 'warning',
                                StockAlertType::SEUIL_PROCHE => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('canal')
                            ->label('Canal')
                            ->badge()
                            ->icon(Heroicon::OutlinedPaperAirplane)
                            ->color(fn (?string $state): string => match ($state) {
                                'Email' => 'info',
                                'SMS' => 'success',
                                'InApp' => 'primary',
                                'Tous' => 'gray',
                                default => 'gray',
                            }),
                    ]),
                Section::make('Traitement')
                    ->icon(Heroicon::OutlinedWrenchScrewdriver)
                    ->columns([
                        'default' => 1,
                        'lg' => 2,
                    ])
                    ->schema([
                        TextEntry::make('retour')
                            ->label('Retour')
                            ->icon(Heroicon::OutlinedChatBubbleBottomCenterText)
                            ->placeholder('Aucun retour renseigné')
                            ->columnSpanFull(),
                        TextEntry::make('note_resolution')
                            ->label('Note de résolution')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->placeholder('Aucune note de résolution')
                            ->columnSpanFull(),
                    ]),
                Section::make('Dates')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        TextEntry::make('date_alerte')
                            ->label("Date d'alerte")
                            ->icon(Heroicon::OutlinedBellAlert)
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('date_traitement')
                            ->label('Date de traitement')
                            ->icon(Heroicon::OutlinedCheckCircle)
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Pas encore traitée'),
                    ]),
            ]);
    }
}
