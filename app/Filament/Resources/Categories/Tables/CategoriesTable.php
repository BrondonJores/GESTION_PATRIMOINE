<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction; 
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Famille;


class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nom_categorie')
                    ->searchable(),
                TextColumn::make('code_categorie')
                    ->searchable(),
              TextColumn::make('famille.nom_famille')
                ->label('Famille')
                ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                  // Filtre par Famille 
                SelectFilter::make('famille_id')
                    ->label('Famille')
                    ->options(
                        Famille::orderBy('nom_famille')
                            ->pluck('nom_famille', 'id')
                            ->toArray()
                    ),
            
            ])
            ->recordActions([
                EditAction::make(),
                        DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->actionsColumnLabel('Actions')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
