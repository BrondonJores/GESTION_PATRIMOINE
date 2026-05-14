<?php

namespace App\Filament\Resources\Notifications\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class NotificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Notification')
                    ->icon(Heroicon::OutlinedBell)
                    ->description('Lecture rapide du message et de son état.')
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                    ])
                    ->schema([
                        TextEntry::make('contenu')
                            ->label('Message')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold)
                            ->columnSpanFull(),
                        TextEntry::make('lu')
                            ->label('État')
                            ->badge()
                            ->formatStateUsing(fn (?bool $state): string => $state ? 'Lue' : 'Non lue')
                            ->color(fn (?bool $state): string => $state ? 'success' : 'warning')
                            ->icon(fn (?bool $state): Heroicon => $state ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedEnvelope),
                        TextEntry::make('canal')
                            ->label('Canal')
                            ->badge()
                            ->icon(Heroicon::OutlinedPaperAirplane)
                            ->color(fn (?string $state): string => match ($state) {
                                'Email' => 'info',
                                'SMS' => 'success',
                                'InApp' => 'primary',
                                'Tous' => 'gray',
                                default => 'gray',
                            }),
                        IconEntry::make('lu')
                            ->label('Confirmation')
                            ->boolean()
                            ->true(Heroicon::OutlinedEye, 'success')
                            ->false(Heroicon::OutlinedEyeSlash, 'warning'),
                    ]),
                Section::make('Contexte')
                    ->icon(Heroicon::OutlinedUser)
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Destinataire')
                            ->icon(Heroicon::OutlinedUserCircle)
                            ->placeholder('Utilisateur indisponible'),
                        TextEntry::make('date_envoi')
                            ->label("Date d'envoi")
                            ->icon(Heroicon::OutlinedCalendarDays)
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Non renseignée'),
                    ]),
            ]);
    }
}
