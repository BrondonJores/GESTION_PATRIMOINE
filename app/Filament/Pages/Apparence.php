<?php

namespace App\Filament\Pages;

use App\Services\AppThemeService;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Apparence extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static string|UnitEnum|null $navigationGroup = 'Support & Admin';

    protected static ?string $navigationLabel = 'Apparence';

    protected static ?string $title = 'Apparence';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.apparence';

    /**
     * @var array<string, string|null>
     */
    public array $data = [];

    public function mount(AppThemeService $theme): void
    {
        $this->form->fill($theme->getTheme());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ColorPicker::make('primary')
                    ->label('Couleur principale')
                    ->required()
                    ->hexColor(),
                ColorPicker::make('gray')
                    ->label('Couleur neutre')
                    ->required()
                    ->hexColor(),
                ColorPicker::make('success')
                    ->label('Couleur succès')
                    ->required()
                    ->hexColor(),
                ColorPicker::make('warning')
                    ->label('Couleur avertissement')
                    ->required()
                    ->hexColor(),
                ColorPicker::make('danger')
                    ->label('Couleur danger')
                    ->required()
                    ->hexColor(),
                ColorPicker::make('info')
                    ->label('Couleur information')
                    ->required()
                    ->hexColor(),
                Select::make('dark_mode')
                    ->label('Mode sombre')
                    ->options([
                        'enabled' => 'Activé',
                        'disabled' => 'Désactivé',
                    ])
                    ->required(),
                Select::make('dark_mode_forced')
                    ->label('Forcer le mode sombre')
                    ->options([
                        'disabled' => 'Non',
                        'enabled' => 'Oui',
                    ])
                    ->required(),
                Select::make('sidebar_width')
                    ->label('Largeur de la sidebar')
                    ->options([
                        '16rem' => 'Compacte',
                        '18rem' => 'Réduite',
                        '20rem' => 'Standard',
                        '22rem' => 'Large',
                        '24rem' => 'Très large',
                    ])
                    ->required(),
                Select::make('collapsed_sidebar_width')
                    ->label('Largeur sidebar repliée')
                    ->options([
                        '4rem' => 'Compacte',
                        '4.5rem' => 'Standard',
                        '5rem' => 'Large',
                    ])
                    ->required(),
            ])
            ->statePath('data');
    }

    public function save(AppThemeService $theme): void
    {
        $theme->saveTheme($this->form->getState());

        Notification::make()
            ->title('Apparence enregistrée')
            ->body('Rechargez la page pour appliquer les nouvelles couleurs au panel.')
            ->success()
            ->send();
    }
}
