<?php

namespace App\Filament\Widgets;

use App\Models\Alerte;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class AlertesActivesWidget extends TableWidget
{
        protected static ?int    $sort    = 40;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Alerte::query())
            ->columns([
                TextColumn::make('statut')
                    ->searchable(),
                TextColumn::make('canal')
                    ->searchable(),
                TextColumn::make('date_alerte')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('date_traitement')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('article_id')
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
