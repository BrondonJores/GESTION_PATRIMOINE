<?php

namespace App\Filament\Resources\Rapports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RapportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type_rapport')
                    ->label('Type de rapport')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('format')
                    ->label('Format')
                    ->badge(),
                TextColumn::make('user.name')
                    ->label('Généré par')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('periode_debut')
                    ->label('Début')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('periode_fin')
                    ->label('Fin')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('chemin_fichier')
                    ->label('Fichier')
                    ->limit(45)
                    ->searchable(),
                TextColumn::make('date_generation')
                    ->label('Date de génération')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('format')
                    ->label('Format')
                    ->options([
                        'PDF' => 'PDF',
                        'Excel' => 'Excel',
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
