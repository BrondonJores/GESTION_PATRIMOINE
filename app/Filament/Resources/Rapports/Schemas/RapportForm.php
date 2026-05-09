<?php

namespace App\Filament\Resources\Rapports\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RapportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Paramètres du rapport')
                    ->schema([
                        Select::make('type_rapport')
                            ->label('Type de rapport')
                            ->options([
                                'Inventaire des articles' => 'Inventaire des articles',
                                'Affectations' => 'Affectations',
                                'Réaffectations' => 'Réaffectations',
                                'Récupérations' => 'Récupérations',
                                'Alertes' => 'Alertes',
                                'Notifications' => 'Notifications',
                                'Utilisateurs' => 'Utilisateurs',
                                'Logs' => 'Logs',
                            ])
                            ->native(false)
                            ->searchable()
                            ->selectablePlaceholder(false)
                            ->required(),
                        Select::make('format')
                            ->label('Format')
                            ->options([
                                'PDF' => 'PDF',
                                'Excel' => 'Excel',
                            ])
                            ->native(false)
                            ->selectablePlaceholder(false)
                            ->default('PDF')
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Informations système')
                    ->description('Ces informations sont générées automatiquement pour éviter les modifications manuelles.')
                    ->schema([
                        TextInput::make('chemin_fichier')
                            ->label('Chemin du fichier')
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(255),
                        Select::make('user_id')
                            ->label('Généré par')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated(false),
                        DateTimePicker::make('date_generation')
                            ->label('Date de génération')
                            ->seconds(false)
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(3),
            ]);
    }
}
