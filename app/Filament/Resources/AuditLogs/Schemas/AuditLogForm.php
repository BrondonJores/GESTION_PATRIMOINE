<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AuditLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('module')
                    ->label('Module')
                    ->required()
                    ->maxLength(100),
                Select::make('action')
                    ->label('Action')
                    ->options([
                        'Création' => 'Création',
                        'Modification' => 'Modification',
                        'Suppression' => 'Suppression',
                        'Connexion' => 'Connexion',
                        'Déconnexion' => 'Déconnexion',
                        'Export' => 'Export',
                        'Alerte' => 'Alerte',
                        'Affectation' => 'Affectation',
                        'Réaffectation' => 'Réaffectation',
                        'Récupération' => 'Récupération',
                    ])
                    ->required(),
                TextInput::make('adresse_ip')
                    ->label('Adresse IP')
                    ->maxLength(50),
                Select::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('date_action')
                    ->label("Date de l'action")
                    ->required(),
            ]);
    }
}
