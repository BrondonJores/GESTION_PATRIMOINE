<?php

namespace App\Filament\Resources\Rapports\Tables;

use App\Filament\Resources\Rapports\RapportResource;
use App\Models\Rapport;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

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
                Action::make('telecharger')
                    ->label('Télécharger')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('success')
                    ->visible(fn (Rapport $record): bool => RapportResource::canDownload($record))
                    ->action(fn (Rapport $record) => Storage::disk('local')->download(
                        $record->chemin_fichier,
                        RapportResource::downloadName($record),
                )),
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
