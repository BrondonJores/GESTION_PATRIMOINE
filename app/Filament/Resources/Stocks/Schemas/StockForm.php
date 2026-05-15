<?php

namespace App\Filament\Resources\Stocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('article_id')
                    ->relationship('article', 'id')
                    ->required(),
                Select::make('statut')
                    ->required()
                    ->options([
                        'Disponible' => 'Disponible',
                        'En_maintenance' => 'En maintenance',
                        'Réformé' => 'Réformé',
                    ]),
                TextInput::make('quantite')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
