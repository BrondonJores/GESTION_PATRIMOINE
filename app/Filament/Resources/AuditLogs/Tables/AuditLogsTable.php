<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('module')
                    ->label('Module')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('action')
                    ->label('Action')
                    ->badge(),
                TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('adresse_ip')
                    ->label('Adresse IP')
                    ->searchable(),
                TextColumn::make('date_action')
                    ->label("Date de l'action")
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('action')
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
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
