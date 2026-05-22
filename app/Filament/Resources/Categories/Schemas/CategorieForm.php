<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CategorieForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code_categorie')
                ->label('Code catégorie'),

        TextInput::make('nom_categorie')
            ->label('Nom catégorie')
            ->required(),

        Select::make('famille_id')
            ->label('Famille')
            ->relationship('famille', 'nom_famille')
            ->searchable()
            ->preload()
            ->required(),
    ]);
}
}
