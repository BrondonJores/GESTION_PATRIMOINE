<?php

namespace App\Filament\Resources\Notifications\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('canal')
                    ->label('Canal')
                    ->options([
                        'Email' => 'E-mail',
                        'SMS' => 'SMS',
                        'InApp' => 'Application',
                        'Tous' => 'Tous',
                    ])
                    ->required(),
                Textarea::make('contenu')
                    ->label('Contenu')
                    ->required()
                    ->columnSpanFull(),
                Checkbox::make('lu')
                    ->label('Notification lue'),
                DateTimePicker::make('date_envoi')
                    ->label("Date d'envoi")
                    ->required(),
            ]);
    }
}
