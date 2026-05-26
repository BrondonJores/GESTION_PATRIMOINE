<?php

namespace App\Filament\Resources\Alertes\Tables;

use App\Models\Alerte;
use App\Services\AlerteStatusService;
use App\Support\Alertes\StockAlertType;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AlertesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('consommable.designation')
                    ->label('Consommable')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type_alerte')
                    ->label("Type d'alerte")
                    ->formatStateUsing(fn (?string $state): string => StockAlertType::label($state))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        StockAlertType::STOCK_EPUISE => 'danger',
                        StockAlertType::STOCK_MINIMAL => 'warning',
                        default => 'gray',
                    }),
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
                Action::make('prendre_en_charge')
                    ->label('Prendre en charge')
                    ->icon(Heroicon::OutlinedClock)
                    ->color('warning')
                    ->visible(fn (Alerte $record): bool => $record->statut === 'Non_traité'
                        && (auth()->user()?->can('update', $record) ?? false))
                    ->action(fn (Alerte $record): Alerte => app(AlerteStatusService::class)->prendreEnCharge($record)),
                Action::make('marquer_resolue')
                    ->label('Marquer résolue')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->schema([
                        Textarea::make('note_resolution')
                            ->label('Note de résolution')
                            ->required()
                            ->maxLength(1000),
                    ])
                    ->visible(fn (Alerte $record): bool => $record->statut !== 'Résolu'
                        && (auth()->user()?->can('update', $record) ?? false))
                    ->action(fn (Alerte $record, array $data): Alerte => app(AlerteStatusService::class)->marquerResolue(
                        $record,
                        $data['note_resolution'] ?? null,
                    )),
            ])
            ->toolbarActions([]);
    }
}
