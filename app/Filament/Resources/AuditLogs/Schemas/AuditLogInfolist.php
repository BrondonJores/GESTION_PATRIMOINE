<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('module')->label('Module'),
                TextEntry::make('action')->label('Action')->badge(),
                TextEntry::make('adresse_ip')->label('Adresse IP'),
                TextEntry::make('user.name')->label('Utilisateur'),
                TextEntry::make('date_action')->label("Date de l'action")->dateTime(),
            ]);
    }
}
