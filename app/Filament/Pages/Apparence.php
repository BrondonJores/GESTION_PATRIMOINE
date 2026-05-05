<?php

namespace App\Filament\Pages;

use App\Services\AppThemeService;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
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
