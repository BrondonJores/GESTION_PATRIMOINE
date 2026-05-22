<?php

namespace App\Filament\Resources\Blocs\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlocsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code_bloc')
                    ->label('Code')
                    ->searchable(),

                TextColumn::make('nom_bloc')
                    ->label('Nom')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),

                ToggleColumn::make('actif')
                    ->label('Actif'),

                TextColumn::make('salles_count')
                    ->label('Nb Salles')
                    ->counts('salles'),
            ])
            ->filters([
                Filter::make('actifs')
                    ->label('Actifs uniquement')
                    ->query(fn (Builder $query) => $query->where('actif', true))
                    ->toggle(),

                Filter::make('inactifs')
                    ->label('Inactifs uniquement')
                    ->query(fn (Builder $query) => $query->where('actif', false))
                    ->toggle(),
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