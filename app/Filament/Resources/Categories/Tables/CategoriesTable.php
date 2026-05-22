<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Article;
use App\Models\Categorie;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                Categorie::query()
                    ->withCount([
                        // Compteurs équipements
                        'articles as total_articles' => fn ($q) =>
                            $q->whereNotIn('statut', [Article::REFORME]),

                        'articles as articles_disponibles' => fn ($q) =>
                            $q->where('statut', Article::DISPONIBLE),

                        'articles as articles_affectes' => fn ($q) =>
                            $q->where('statut', Article::AFFECTE),

                        'articles as articles_maintenance' => fn ($q) =>
                            $q->where('statut', Article::MAINTENANCE),

                        'articles as articles_reformes' => fn ($q) =>
                            $q->where('statut', Article::REFORME),

                        // Compteurs consommables
                        'consommables as total_consommables',

                        'consommables as consos_disponibles' => fn ($q) =>
                            $q->where('statut', 'Disponible'),

                        'consommables as consos_sous_seuil' => fn ($q) =>
                            $q->where('statut', 'Sous seuil'),

                        'consommables as consos_epuises' => fn ($q) =>
                            $q->where('statut', 'Épuisé'),

                        // Stock total en unités pour les consommables
                        'consommables as stock_total_unites' => fn ($q) =>
                            $q->selectRaw('SUM(quantite_stock)'),
                    ])
                    ->with('famille')
            )

            ->columns([

                //  Colonnes communes (toujours visibles)

                TextColumn::make('famille.nom_famille')
                    ->label('Famille')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nom_categorie')
                    ->label('Catégorie')
                    ->searchable()
                    ->sortable(),

                // ── Colonnes équipements ───────────────────────────
                // Visibles uniquement quand le filtre type = equipements

                TextColumn::make('total_articles')
                    ->label('Total actifs')
                    ->badge()
                    ->color('primary')
                    ->visible(fn () =>
                        self::getFiltreTypeActif() === 'equipements'
                    ),

                TextColumn::make('articles_disponibles')
                    ->label('Disponibles')
                    ->badge()
                    ->color('success')
                    ->visible(fn () =>
                        self::getFiltreTypeActif() === 'equipements'
                    ),

                TextColumn::make('articles_affectes')
                    ->label('Affectés')
                    ->badge()
                    ->color('warning')
                    ->visible(fn () =>
                        self::getFiltreTypeActif() === 'equipements'
                    ),

                TextColumn::make('articles_maintenance')
                    ->label('Maintenance')
                    ->badge()
                    ->color('gray')
                    ->visible(fn () =>
                        self::getFiltreTypeActif() === 'equipements'
                    ),

                TextColumn::make('articles_reformes')
                    ->label('Réformés')
                    ->badge()
                    ->color('danger')
                    ->visible(fn () =>
                        self::getFiltreTypeActif() === 'equipements'
                    ),

                // ── Colonnes consommables ──────────────────────────
                // Visibles uniquement quand le filtre type = consommables

                TextColumn::make('total_consommables')
                    ->label('Références')
                    ->badge()
                    ->color('warning')
                    ->visible(fn () =>
                        self::getFiltreTypeActif() === 'consommables'
                    ),

                TextColumn::make('stock_total_unites')
                    ->label('Unités en stock')
                    ->badge()
                    ->color('primary')
                    ->visible(fn () =>
                        self::getFiltreTypeActif() === 'consommables'
                    ),

                TextColumn::make('consos_disponibles')
                    ->label('Disponibles')
                    ->badge()
                    ->color('success')
                    ->visible(fn () =>
                        self::getFiltreTypeActif() === 'consommables'
                    ),

                TextColumn::make('consos_sous_seuil')
                    ->label('Sous seuil')
                    ->badge()
                    ->color('warning')
                    ->visible(fn () =>
                        self::getFiltreTypeActif() === 'consommables'
                    ),

                TextColumn::make('consos_epuises')
                    ->label('Épuisés')
                    ->badge()
                    ->color('danger')
                    ->visible(fn () =>
                        self::getFiltreTypeActif() === 'consommables'
                    ),
            ])

            ->filters([

                // Filtre principal — détermine quelles colonnes sont visibles
                // default('equipements') = filtre actif dès le chargement
                SelectFilter::make('type_categorie')
                    ->label('Voir les catégories de type')
                    ->options([
                        'equipements'  => 'Équipements (tables, chaises...)',
                        'consommables' => 'Consommables (marqueurs, papier...)',
                    ])
                    ->default('equipements') // ← filtre actif par défaut
                    ->query(function (Builder $query, array $data): Builder {
                        if (!filled($data['value'])) return $query;

                        return match ($data['value']) {
                            // Catégories qui ont des articles
                            'equipements' => $query->whereHas('articles'),

                            // Catégories qui ont des consommables
                            'consommables' => $query->whereHas('consommables'),

                            default => $query,
                        };
                    }),

                SelectFilter::make('famille_id')
                    ->label('Famille')
                    ->relationship('famille', 'nom_famille'),

            
            ])

            ->recordActions([
                EditAction::make(),
            ])
            ->actionsColumnLabel('Actions')
            ->defaultSort('nom_categorie');
    }

    // Lire la valeur du filtre type depuis la session Livewire
    // Filament stocke les filtres dans la session du composant
    private static function getFiltreTypeActif(): string
    {
        // Récupérer les filtres actifs depuis la requête courante
        $filtres = request()->get('tableFilters', []);

        return $filtres['type_categorie']['value'] ?? 'equipements';
    }
}