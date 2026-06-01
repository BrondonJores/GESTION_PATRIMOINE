<?php

namespace App\Filament\Pages;

use App\Services\ContactSettingService;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use BackedEnum;

class ContactSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static UnitEnum|string|null $navigationGroup = 'Support & Admin';

    protected static ?string $navigationLabel = 'Contacts & Alertes';

    protected static ?string $title = 'Configuration des Contacts';

    protected static ?int $navigationSort = 95;

    protected string $view = 'filament.pages.contact-settings';

    /**
     * @var array<string, string|null>
     */
    public array $data = [];

    public function mount(ContactSettingService $service): void
    {
        $this->form->fill($service->getSettings());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité de l\'émetteur')
                    ->description('Ces informations seront affichées comme expéditeur des emails et SMS.')
                    ->icon(Heroicon::OutlinedUser)
                    ->schema([
                        TextInput::make('nom_emetteur')
                            ->label('Nom de l\'émetteur')
                            ->placeholder('Ex: Gestion Patrimoine IFTTS')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('email_emetteur')
                            ->label('Email de l\'émetteur')
                            ->placeholder('Ex: alertes@iftts.com')
                            ->email()
                            ->required()
                            ->maxLength(100),
                        TextInput::make('telephone_emetteur')
                            ->label('Téléphone de l\'émetteur')
                            ->placeholder('Ex: +225 0102030405')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2),

                Section::make('Configuration SMS (Twilio)')
                    ->description('Paramètres requis pour l\'envoi réel des SMS via l\'API Twilio.')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->collapsible()
                    ->schema([
                        TextInput::make('twilio_sid')
                            ->label('Twilio Account SID')
                            ->placeholder('AC...')
                            ->password()
                            ->revealable(),
                        TextInput::make('twilio_token')
                            ->label('Twilio Auth Token')
                            ->placeholder('Votre token Twilio')
                            ->password()
                            ->revealable(),
                        TextInput::make('twilio_number')
                            ->label('Numéro Twilio')
                            ->placeholder('Ex: +1234567890')
                            ->tel(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(ContactSettingService $service): void
    {
        $state = $this->form->getState();

        $service->saveSettings($state);

        Notification::make()
            ->title('Paramètres enregistrés')
            ->body('Les configurations de contact et d\'alertes ont été mises à jour.')
            ->success()
            ->send();
    }
}
