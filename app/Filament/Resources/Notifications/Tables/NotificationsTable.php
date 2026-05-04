<?php

namespace App\Filament\Resources\Notifications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('canal')
                    ->label('Canal')
                    ->badge(),
                TextColumn::make('contenu')
                    ->label('Contenu')
                    ->limit(60)
                    ->searchable(),
                IconColumn::make('lu')
                    ->label('Lue')
                    ->boolean(),
                TextColumn::make('date_envoi')
                    ->label("Date d'envoi")
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('canal')
                    ->label('Canal')
                    ->options([
                        'Email' => 'E-mail',
                        'SMS' => 'SMS',
                        'InApp' => 'Application',
                        'Tous' => 'Tous',
                    ]),
                TernaryFilter::make('lu')
                    ->label('Lecture'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
