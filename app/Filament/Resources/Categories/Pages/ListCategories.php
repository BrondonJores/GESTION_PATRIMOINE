<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategorieResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Models\Categorie;

class ListCategories extends ListRecords
{
    protected static string $resource = CategorieResource::class;

    public function getTitle(): string
    {
        return 'Catégories';
    }

    protected function getHeaderActions(): array
    {
        return [
            // Bouton vers stats équipements
            Action::make('voir_equipements')
                ->label('Catégories équipements')
                ->icon('heroicon-m-archive-box')
                ->color('primary')
                ->url(CategorieResource::getUrl('equipements')),

            // Bouton vers stats consommables
            Action::make('voir_consommables')
                ->label('Catégories consommables')
                ->icon('heroicon-m-beaker')
                ->color('warning')
                ->url(CategorieResource::getUrl('consommables')),

            CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Toutes les catégories avec leur famille
                Categorie::query()
                    ->with('famille')
                    // Savoir si la catégorie est peuplée
                    ->withCount([
                        'articles as nb_articles',
                        'consommables as nb_consommables',
                    ])
            )
            ->columns([
                
                TextColumn::make('nom_categorie')
                    ->label('Catégorie')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('famille.nom_famille')
                    ->label('Famille')
                    ->sortable()
                    ->searchable(),

         
            ])
            ->filters([
                SelectFilter::make('famille_id')
                    ->label('Famille')
                    ->relationship('famille', 'nom_famille'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->actionsColumnLabel('Actions')
            ->defaultSort('nom_categorie');
    }
}