<?php
// app/Filament/Resources/Categories/Pages/ListCategoriesEquipements.php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategorieResource;
use App\Models\Article;
use App\Models\Categorie;
use Filament\Actions\Action;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;

class ListCategoriesEquipements extends ListRecords
{
    protected static string $resource = CategorieResource::class;

    // Titre de la page
    public function getTitle(): string
    {
        return 'Catégories — Équipements';
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
                   CreateAction::make()
            ->visible(fn () => Auth::user()?->can('create categories') ?? false),
            // Lien vers la page consommables
            \Filament\Actions\Action::make('voir_consommables')
                ->label('Voir les consommables')
                ->icon('heroicon-m-beaker')
                ->color('warning')
                ->url(CategorieResource::getUrl('consommables')),

            
        ];
    }

    // Surcharger la table pour cette page spécifiquement
    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Uniquement les catégories qui ont des articles
                Categorie::query()
                    ->whereHas('articles')
                    ->withCount([
                        'articles as total_actifs' => fn ($q) =>
                            $q->whereNotIn('statut', [Article::REFORME]),

                        'articles as disponibles' => fn ($q) =>
                            $q->where('statut', Article::DISPONIBLE),

                        'articles as affectes' => fn ($q) =>
                            $q->where('statut', Article::AFFECTE),

                        'articles as maintenance' => fn ($q) =>
                            $q->where('statut', Article::MAINTENANCE),

                        'articles as reformes' => fn ($q) =>
                            $q->where('statut', Article::REFORME),
                    ])
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

                TextColumn::make('total_actifs')
                    ->label('Total actifs')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('disponibles')
                    ->label('Disponibles')
                    ->badge()
                    ->color('success'),

                TextColumn::make('affectes')
                    ->label('Affectés')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('maintenance')
                    ->label('Maintenance')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('reformes')
                    ->label('Réformés')
                    ->badge()
                    ->color('danger'),
            ])
            ->filters([
                SelectFilter::make('famille_id')
                    ->label('Famille')
                    ->relationship('famille', 'nom_famille'),
            ])
            ->recordActions([
                EditAction::make()
        ->visible(fn () =>
            Auth::user()?->can('update categories') ?? false
        ),

    DeleteAction::make()
        ->requiresConfirmation()
        ->visible(fn () =>
            Auth::user()?->can('delete categories') ?? false
        ->requiresConfirmation(),),
            ])
            ->actionsColumnLabel('Actions')
            ->defaultSort('nom_categorie');
    }
}