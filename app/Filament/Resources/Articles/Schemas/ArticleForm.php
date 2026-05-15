<?php

namespace App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ArticleForm
{
   public static function configure(Schema $schema): Schema
{
    return $schema->components([

        TextInput::make('numero_reference')
            ->label('Numéro de référence')
            ->required()
            ->unique(ignoreRecord: true),

        
        TextInput::make('designation')
            ->label('Désignation')
            ->required(),

        TextInput::make('code_ancien')
            ->label('Code ancien'),

        TextInput::make('quantite_totale')
            ->label('Quantité totale')
            ->numeric()
            ->required(),

        TextInput::make('quantite_min')
            ->label('Quantité minimale')
            ->numeric(),

        Select::make('categorie_id')
            ->label('Catégorie')
            ->relationship('categorie', 'nom_categorie')
            ->searchable()
            ->preload()
            ->required(),

        Textarea::make('observations')
            ->label('Observations')
            ->columnSpanFull(),
    ]);
}
}
