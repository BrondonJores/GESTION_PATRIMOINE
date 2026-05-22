<?php

namespace App\Filament\Resources\Consommables\Tables;

use App\Models\Consommable;
use App\Models\Categorie;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class ConsommablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
             
                TextColumn::make('designation')
                    ->label('Désignation')
                    ->searchable(),

                TextColumn::make('categorie.nom_categorie')
                    ->label('Catégorie'),

                TextColumn::make('quantite_stock')
                    ->label('Stock actuel')
                    ->badge()
                    ->color(fn (Consommable $r) => match (true) {
                        $r->quantite_stock <= 0 => 'danger',
                        $r->quantite_stock <= $r->quantite_min => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('quantite_min')
                    ->label('Seuil minimal')
                    ->placeholder('—')
                    ->sortable(),
                    
                TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Disponible' => 'success',
                        'Sous seuil' => 'warning',
                        'Épuisé' => 'danger',
                        default => 'gray',
                    }),
            ])

            ->filters([
                SelectFilter::make('statut')
                    ->options([
                        'Disponible' => 'Disponible',
                        'Sous seuil' => 'Sous seuil',
                        'Épuisé' => 'Épuisé',
                    ]),

                SelectFilter::make('categorie_id')
                    ->relationship('categorie', 'nom_categorie'),
            ])

            ->recordActions([
                EditAction::make(),

                Action::make('reapprovisionner')
                    ->label('Réapprovisionner')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('quantite')
                            ->numeric()
                            ->required(),

                        \Filament\Forms\Components\Textarea::make('motif')
                            ->required(),
                    ])
                    ->action(function (Consommable $record, array $data) {
                        $record->increment('quantite_stock', $data['quantite']);

                        Notification::make()
                            ->title('Stock mis à jour')
                            ->success()
                            ->send();
                    }),
            ])
            ->actionsColumnLabel('Actions')
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}