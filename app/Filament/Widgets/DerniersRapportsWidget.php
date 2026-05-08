<?php

namespace App\Filament\Widgets;

use App\Models\Rapport;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class DerniersRapportsWidget extends TableWidget
{
        protected static ?int    $sort    = 60;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Rapport::query())
            ->columns([
                TextColumn::make('type_rapport')
                    ->searchable(),
                TextColumn::make('chemin_fichier')
                    ->searchable(),
                TextColumn::make('format')
                    ->searchable(),
                TextColumn::make('date_generation')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
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
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
