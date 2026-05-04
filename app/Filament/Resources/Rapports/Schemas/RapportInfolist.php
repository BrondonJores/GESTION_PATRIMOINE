<?php

namespace App\Filament\Resources\Rapports\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RapportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('type_rapport')->label('Type de rapport'),
                TextEntry::make('format')->label('Format')->badge(),
                TextEntry::make('chemin_fichier')->label('Chemin du fichier'),
                TextEntry::make('user.name')->label('Généré par'),
                TextEntry::make('date_generation')->label('Date de génération')->dateTime(),
            ]);
    }
}
