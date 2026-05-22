<?php
// app/Filament/Resources/Categories/Pages/ListCategoriesConsommables.php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategorieResource;
use App\Models\Categorie;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\Action;

use Filament\Actions\EditAction;

class ListCategoriesConsommables extends ListRecords
{
    protected static string $resource = CategorieResource::class;

    public function getTitle(): string
    {
        return 'Catégories — Consommables';
    }

    protected function getHeaderActions(): array
    {
        return [
              // Retour vers toutes les catégories
            Action::make('toutes_categories')
                ->label('Toutes les catégories')
                ->icon('heroicon-m-list-bullet')
                ->color('gray')
                ->url(CategorieResource::getUrl('index')),
            // Lien retour vers équipements
            \Filament\Actions\Action::make('voir_equipements')
                ->label('Voir les équipements')
                ->icon('heroicon-m-archive-box')
                ->color('primary')
                ->url(CategorieResource::getUrl('index')),

            \Filament\Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Uniquement les catégories qui ont des consommables
                Categorie::query()
                    ->whereHas('consommables')
                    ->withCount([
                        'consommables as total_references',

                        'consommables as references_disponibles' => fn ($q) =>
                            $q->where('statut', 'Disponible'),

                        'consommables as references_sous_seuil' => fn ($q) =>
                            $q->where('statut', 'Sous seuil'),

                        'consommables as references_epuisees' => fn ($q) =>
                            $q->where('statut', 'Épuisé'),
                    ])
                    // Somme des unités en stock pour chaque catégorie
                    ->withSum('consommables as stock_total_unites', 'quantite_stock')
                    ->with('famille')
            )
            ->columns([
                TextColumn::make('famille.nom_famille')
                    ->label('Famille')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nom_categorie')
                    ->label('Catégorie')
                    ->searchable()
                    ->sortable(),

                // Nombre de références dans cette catégorie
                TextColumn::make('total_references')
                    ->label('Total références')
                    ->badge()
                    ->color('primary'),

                // Somme des unités physiques disponibles
                TextColumn::make('stock_total_unites')
                    ->label('Unités en stock')
                    ->badge()
                    ->color('info'),

                TextColumn::make('references_disponibles')
                    ->label('Disponibles')
                    ->badge()
                    ->color('success'),

                TextColumn::make('references_sous_seuil')
                    ->label('Sous seuil')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('references_epuisees')
                    ->label('Épuisées')
                    ->badge()
                    ->color('danger'),
            ])
            ->filters([
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
}