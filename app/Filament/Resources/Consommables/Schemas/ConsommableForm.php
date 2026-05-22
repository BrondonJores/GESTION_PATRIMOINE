<?php

namespace App\Filament\Resources\Consommables\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;

class ConsommableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
       
            TextInput::make('designation')
                ->label('Désignation')
                ->required(),

            Select::make('categorie_id')
                ->label('Catégorie')
                ->relationship('categorie', 'nom_categorie')
                ->searchable()->preload()->required(),

             TextInput::make('quantite_stock')
                ->label('Quantité en stock')
                ->numeric()
                ->minValue(0)
                ->required()
                ->rules([
                    // Interdire les quantités négatives
                    'min:0',
                ])
                ->helperText('Quantité physique actuellement disponible.'),
          TextInput::make('quantite_min')
                ->label('Seuil minimal d\'alerte')
                ->numeric()
                ->minValue(0) // Interdire un seuil négatif
                ->rules(['min:0'])
                ->helperText('Alerte déclenchée quand le stock atteint ce seuil.'),
             Select::make('statut')
                ->label('Statut')
                ->options([
                    'Disponible'    => 'Disponible',
                    'Sous seuil'       => 'Sous seuil',
                    'Épuisé' => 'Épuisé',
                ])
                ->default('Disponible')
                ->required(),
         
            Textarea::make('observations')->label('Observations')->rows(3),
        ]);
    }
}
