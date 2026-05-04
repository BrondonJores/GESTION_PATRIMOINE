<?php

namespace App\Filament\Resources\Alertes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AlertesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('article.designation')
                    ->label('Article')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Résolu' => 'success',
                        'En_cours' => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('canal')
                    ->label('Canal')
                    ->badge(),
                TextColumn::make('date_alerte')
                    ->label("Date d'alerte")
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('date_traitement')
                    ->label('Traitée le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'Non_traité' => 'Non traité',
                        'En_cours' => 'En cours',
                        'Résolu' => 'Résolu',
                    ]),
                SelectFilter::make('canal')
                    ->label('Canal')
                    ->options([
                        'Email' => 'Email',
                        'SMS' => 'SMS',
                        'InApp' => 'InApp',
                        'Tous' => 'Tous',
                    ]),
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
