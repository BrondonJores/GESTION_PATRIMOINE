<?php

namespace App\Filament\Resources\Familles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FamilleForm
{
    public static function configure(Schema $schema): Schema
{
    return $schema->components([

        TextInput::make('code_famille')
            ->label('Code famille')
            ->required(),

        TextInput::make('nom_famille')
            ->label('Nom famille')
            ->required(),
    ]);
    }
}
