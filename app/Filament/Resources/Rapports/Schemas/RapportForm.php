<?php

namespace App\Filament\Resources\Rapports\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RapportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('type_rapport')
                    ->label('Type de rapport')
                    ->required()
                    ->maxLength(100),
                Select::make('format')
                    ->label('Format')
                    ->options([
                        'PDF' => 'PDF',
                        'Excel' => 'Excel',
                    ])
                    ->required(),
                TextInput::make('chemin_fichier')
                    ->label('Chemin du fichier')
                    ->maxLength(255),
                Select::make('user_id')
                    ->label('Généré par')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('date_generation')
                    ->label('Date de génération')
                    ->required(),
            ]);
    }
}
