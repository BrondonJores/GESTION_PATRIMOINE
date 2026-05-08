<?php

namespace App\Filament\Widgets;

use App\Models\Notification;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class DernieresNotificationsWidget extends TableWidget
{
        protected static ?int    $sort    = 50;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Notification::query())
            ->columns([
                TextColumn::make('canal')
                    ->searchable(),
                IconColumn::make('lu')
                    ->boolean(),
                TextColumn::make('date_envoi')
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
