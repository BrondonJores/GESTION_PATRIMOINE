<?php

namespace App\Filament\Resources\Notifications\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class NotificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')->label('Utilisateur'),
                TextEntry::make('canal')->label('Canal')->badge(),
                TextEntry::make('contenu')->label('Contenu'),
                IconEntry::make('lu')->label('Lue')->boolean(),
                TextEntry::make('date_envoi')->label("Date d'envoi")->dateTime(),
            ]);
    }
}
