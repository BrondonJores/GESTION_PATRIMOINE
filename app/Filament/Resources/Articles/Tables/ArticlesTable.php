<?php

namespace App\Filament\Resources\Articles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Models\Article;
use App\Models\Famille;
use App\Models\Stock;
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
                
                TextColumn::make('designation')
                    ->searchable(),

                TextColumn::make('code_ancien')
                    ->label('Code ancien')
                    ->searchable(),

                TextColumn::make('quantite_totale')
                    ->label('Quantité totale')
                    ->sortable(),

                TextColumn::make('quantite_min')
                    ->label('Seuil minimum')
                    ->sortable(),


                TextColumn::make('categorie.nom_categorie')
                    ->label('Catégorie')
                    ->searchable(),

                TextColumn::make('categorie.famille.nom_famille')
                    ->label('Famille')
                    ->searchable(),

                // Statut global calculé
                TextColumn::make('statut_global')
                    ->label('Statut')
                    ->getStateUsing(fn (Article $record) =>
                        $record->is_archived ? 'Archivé' : 'Actif')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Actif'   => 'success',
                        'Archivé' => 'danger',
                        default   => 'gray',
                    }),

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

                 // Filtre Actifs / Archivés
                SelectFilter::make('is_archived')
                    ->label('Statut')
                    ->options([
                        '0' => 'Actifs uniquement',
                        '1' => 'Archivés uniquement',
                    ]),
                
                // Filtre articles sous seuil minimal
        SelectFilter::make('niveau_stock')
                    ->label('Niveau de stock')
                    ->options([
                        'epuise'  => '🔴 Stock épuisé (disponible = 0)',
                        'minimal' => '🟠 Stock minimal (sous le seuil)',
                        'faible'  => '🟡 Stock faible (proche du seuil)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!filled($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {

                            // Stock épuisé : ligne Disponible existe avec quantite = 0
                            // OU ligne Disponible absente (article jamais initialisé)
                            'epuise' => $query
                                ->where('is_archived', false)
                                ->where(function (Builder $q) {
                                    $q->whereExists(fn ($sub) =>
                                        $sub->from('stocks')
                                            ->whereColumn('stocks.article_id', 'articles.id')
                                            ->where('stocks.statut', 'Disponible')
                                            ->where('stocks.quantite', 0)
                                    )
                                    ->orWhereNotExists(fn ($sub) =>
                                        $sub->from('stocks')
                                            ->whereColumn('stocks.article_id', 'articles.id')
                                            ->where('stocks.statut', 'Disponible')
                                    );
                                }),

                            // Stock minimal : disponible > 0 ET <= quantite_min
                            // Nécessite que quantite_min soit défini
                            'minimal' => $query
                                ->where('is_archived', false)
                                ->whereNotNull('quantite_min')
                                ->whereExists(fn ($sub) =>
                                    $sub->from('stocks')
                                        ->whereColumn('stocks.article_id', 'articles.id')
                                        ->where('stocks.statut', 'Disponible')
                                        ->where('stocks.quantite', '>', 0)
                                        ->whereRaw('stocks.quantite <= articles.quantite_min')
                                ),

                            // Stock faible : disponible > quantite_min ET <= quantite_min * 2
                            // C'est la zone d'avertissement précoce
                            'faible' => $query
                                ->where('is_archived', false)
                                ->whereNotNull('quantite_min')
                                ->whereExists(fn ($sub) =>
                                    $sub->from('stocks')
                                        ->whereColumn('stocks.article_id', 'articles.id')
                                        ->where('stocks.statut', 'Disponible')
                                        ->whereRaw('stocks.quantite > articles.quantite_min')
                                        ->whereRaw('stocks.quantite <= articles.quantite_min * 2')
                                ),

                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->actionsColumnLabel('Actions')
            ->toolbarActions([
                BulkActionGroup::make([]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
