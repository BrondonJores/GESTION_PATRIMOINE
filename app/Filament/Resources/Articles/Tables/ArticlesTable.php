<?php

namespace App\Filament\Resources\Articles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Services\ArticleService;
use App\Models\Article;
use App\Models\Famille;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;


class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('numero_reference')
                    ->label('N° Référence')
                    ->searchable(),

                TextColumn::make('code_ancien')
                    ->label('Code ancien')
                    ->searchable(),
                TextColumn::make('designation')
                    ->searchable(),

                TextColumn::make('quantite')
                    ->label('Quantité')
                    ->sortable(),

                TextColumn::make('quantite_min')
                    ->label('Seuil minimum')
                    ->sortable(),


                TextColumn::make('statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Disponible' => 'success',
                        'Affecté' => 'warning',
                        'En_maintenance' => 'gray',
                        'Réformé' => 'danger',
                    }),

                TextColumn::make('etat')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Neuf' => 'success',
                        'Bon' => 'primary',
                        'Usagé' => 'warning',
                        'En_panne' => 'danger',
                        'Réformé' => 'gray',
                    }),


                TextColumn::make('categorie.nom_categorie')
                    ->label('Catégorie')
                    ->searchable(),

                TextColumn::make('categorie.famille.nom_famille')
                    ->label('Famille')
                    ->searchable(),

            ])
            ->filters([

                // Filtre par Famille
                SelectFilter::make('famille')
                    ->label('Famille')
                    ->options(
                        Famille::orderBy('nom_famille')
                            ->pluck('nom_famille', 'id')
                            ->toArray()
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        // On filtre via la relation categorie → famille
                        if (filled($data['value'])) {
                            $query->whereHas('categorie', function (Builder $q) use ($data) {
                                $q->where('famille_id', $data['value']);
                            });
                        }
                        return $query;
                    }),

                // Filtre par Catégorie
                SelectFilter::make('categorie_id')
                    ->label('Catégorie')
                    ->relationship('categorie', 'nom_categorie'),

                // Filtre par Statut
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'Disponible'     => 'Disponible',
                        'Affecté'        => 'Affecté',
                        'En_maintenance' => 'En maintenance',
                        'Réformé'        => 'Réformé',
                    ]),

                // Filtre par État
                // Cahier des charges : "État — Neuf / Bon état / Usagé / En panne / Réformé"
                SelectFilter::make('etat')
                    ->label('État')
                    ->options([
                        'Neuf'     => 'Neuf',
                        'Bon'      => 'Bon',
                        'Usagé'    => 'Usagé',
                        'En_panne' => 'En panne',
                        'Réformé'  => 'Réformé',
                    ]),

                // Filtre articles sous seuil minimal
                Filter::make('sous_seuil')
                    ->label('Sous seuil minimal')
                    ->query(function (Builder $query): Builder {
                        return $query
                            ->whereNotNull('quantite_min')
                            ->whereColumn('quantite', '<=', 'quantite_min');
                    })
                    ->toggle(), // ← bouton on/off simple
            ])
            ->recordActions([
                EditAction::make(),
                //suppression personnalisée avec validation métier
DeleteAction::make()
    ->label('Archiver')
    ->modalHeading('Archiver l\'article')
    ->modalDescription('Le stock restant sera mis à zéro.')
    ->modalSubmitActionLabel('Confirmer l\'archivage')
    ->visible(fn (Article $record): bool => $record->statut !== 'Réformé') // ← disparaît si Réformé
    ->action(function (Article $record) {
        app(ArticleService::class)->supprimer($record);

        Notification::make()
            ->title('Article archivé')
            ->success()
            ->send();
    }),
            ])
            ->actionsColumnLabel('Actions')
            ->toolbarActions([
                // ajouter export plus tard si besoin
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
