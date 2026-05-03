<?php

namespace App\Filament\Resources\Blocs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BlocForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code_bloc')
                ->label('Code')
                ->required()
                ->maxLength(50),

            TextInput::make('nom_bloc')
                ->label('Nom du bloc')
                ->required()
                ->maxLength(100),

            Textarea::make('description')
                ->label('Description')
                ->rows(3),

            Select::make('actif')
                ->label('Statut')
                ->options([
                    true  => 'Actif',
                    false => 'Inactif',
                ])
                ->default(true)
                ->required(),
        ]);
    }
}