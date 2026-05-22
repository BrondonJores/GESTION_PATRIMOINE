<?php

namespace App\Filament\Resources\Alertes\Schemas;

use App\Support\Alertes\StockAlertType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AlerteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('article_id')
                    ->label('Article')
                    ->relationship('article', 'designation')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'Non_traité' => 'Non traité',
                        'En_cours' => 'En cours',
                        'Résolu' => 'Résolu',
                    ])
                    ->required(),
                Select::make('canal')
                    ->label('Canal')
                    ->options([
                        'Email' => 'Email',
                        'SMS' => 'SMS',
                        'InApp' => 'InApp',
                        'Tous' => 'Tous',
                    ])
                    ->required(),
                Select::make('type_alerte')
                    ->label("Type d'alerte")
                    ->options(StockAlertType::labels())
                    ->required(),
                Textarea::make('retour')
                    ->label('Retour')
                    ->columnSpanFull(),
                Textarea::make('note_resolution')
                    ->label('Note de résolution')
                    ->columnSpanFull(),
                DateTimePicker::make('date_alerte')
                    ->label("Date d'alerte")
                    ->required(),
                DateTimePicker::make('date_traitement')
                    ->label('Date de traitement'),
            ]);
    }
}
