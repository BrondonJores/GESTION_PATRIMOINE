<?php

namespace App\Filament\Resources\Salles\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SallesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code_salle')
                    ->label('Code')
                    ->searchable(),

                TextColumn::make('nom_salle')
                    ->label('Nom')
                    ->searchable(),

                TextColumn::make('bloc.nom_bloc')
                    ->label('Bloc')
                    ->sortable(),

                

                ToggleColumn::make('actif')
                    ->label('Active'),
            ])
            ->filters([
                SelectFilter::make('bloc_id')
                    ->label('Bloc')
                    ->relationship('bloc', 'nom_bloc'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->actionsColumnLabel('Actions')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}