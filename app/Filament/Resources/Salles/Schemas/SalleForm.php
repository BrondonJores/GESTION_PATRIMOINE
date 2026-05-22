<?php

namespace App\Filament\Resources\Salles\Schemas;

use App\Models\Bloc;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SalleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code_salle')
                ->label('Code')
                ->required()
                ->maxLength(50),

            TextInput::make('nom_salle')
                ->label('Nom de la salle')
                ->required()
                ->maxLength(100),

           TextInput::make('capacite')
                   ->label('Capacité')
                  ->numeric()
                  ->minValue(1)
                  ->required(),

            Select::make('bloc_id')
                ->label('Bloc')
                ->options(
                    Bloc::where('actif', true)
                        ->pluck('nom_bloc', 'id')
                        ->toArray()
                )
                ->searchable()
                ->required(),

            Select::make('actif')
                ->label('Statut')
                ->options([
                    true  => 'Active',
                    false => 'Inactive',
                ])
                ->default(true)
                ->required(),
        ]);
    }
}