<?php

namespace App\Filament\Resources\Alertes\Schemas;

use App\Support\Alertes\StockAlertType;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AlerteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('article.designation')->label('Article'),
                TextEntry::make('type_alerte')
                    ->label("Type d'alerte")
                    ->formatStateUsing(fn (?string $state): string => StockAlertType::label($state))
                    ->badge(),
                TextEntry::make('statut')->label('Statut')->badge(),
                TextEntry::make('canal')->label('Canal')->badge(),
                TextEntry::make('retour')->label('Retour'),
                TextEntry::make('note_resolution')->label('Note de résolution'),
                TextEntry::make('date_alerte')->label("Date d'alerte")->dateTime(),
                TextEntry::make('date_traitement')->label('Date de traitement')->dateTime(),
            ]);
    }
}
