<?php

namespace App\Filament\Resources\Salles\Schemas;

use App\Models\Bloc;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SalleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code_salle')
                    ->label('Code Salle')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->placeholder('Ex: S-101'),

                TextInput::make('nom_salle')
                    ->label('Nom de la Salle')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Ex: Salle Informatique 1'),

                Select::make('bloc_id')
                    ->label('Bloc')
                    ->required()
                    ->options(Bloc::where('actif', true)->pluck('nom_bloc', 'id'))
                    ->searchable()
                    ->placeholder('Sélectionner un bloc'),

                Toggle::make('actif')
                    ->label('Actif')
                    ->default(true),
            ]);
    }
}