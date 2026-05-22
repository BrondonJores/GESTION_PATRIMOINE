<?php

namespace App\Filament\Pages;

use App\Services\AppThemeService;
use App\Services\Reports\ReportTheme;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
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

    public function mount(AppThemeService $theme, ReportTheme $reportTheme): void
    {
        $this->form->fill([
            ...$theme->getTheme(),
            ...$reportTheme->identity(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thème rapide')
                    ->description('Choisissez une base cohérente, puis ajustez les détails si nécessaire.')
                    ->icon(Heroicon::OutlinedSwatch)
                    ->schema([
                        ToggleButtons::make('preset')
                            ->label('Palette')
                            ->options([
                                'amber' => 'Ambre',
                                'blue' => 'Bleu',
                                'green' => 'Vert',
                                'slate' => 'Sobre',
                            ])
                            ->icons([
                                'amber' => Heroicon::OutlinedSun,
                                'blue' => Heroicon::OutlinedBuildingOffice,
                                'green' => Heroicon::OutlinedShieldCheck,
                                'slate' => Heroicon::OutlinedMoon,
                            ])
                            ->colors([
                                'amber' => 'warning',
                                'blue' => 'info',
                                'green' => 'success',
                                'slate' => 'gray',
                            ])
                            ->grouped()
                            ->live()
                            ->afterStateUpdated(fn (?string $state) => $this->applyPreset($state)),
                    ]),
                Section::make('Couleurs avancées')
                    ->description('Ces couleurs alimentent les boutons, badges, alertes et états du panel.')
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->collapsible()
                    ->schema([
                        ColorPicker::make('primary')
                            ->label('Principale')
                            ->required()
                            ->hexColor(),
                        ColorPicker::make('gray')
                            ->label('Neutre')
                            ->required()
                            ->hexColor(),
                        ColorPicker::make('success')
                            ->label('Succès')
                            ->required()
                            ->hexColor(),
                        ColorPicker::make('warning')
                            ->label('Avertissement')
                            ->required()
                            ->hexColor(),
                        ColorPicker::make('danger')
                            ->label('Danger')
                            ->required()
                            ->hexColor(),
                        ColorPicker::make('info')
                            ->label('Information')
                            ->required()
                            ->hexColor(),
                    ])
                    ->columns([
                        'md' => 2,
                        'xl' => 3,
                    ]),
                Section::make('Navigation et affichage')
                    ->description('Réglages appliqués après rechargement complet du panel.')
                    ->icon(Heroicon::OutlinedBars3)
                    ->schema([
                        ToggleButtons::make('dark_mode')
                            ->label('Mode sombre')
                            ->options([
                                'enabled' => 'Activé',
                                'disabled' => 'Désactivé',
                            ])
                            ->icons([
                                'enabled' => Heroicon::OutlinedMoon,
                                'disabled' => Heroicon::OutlinedSun,
                            ])
                            ->colors([
                                'enabled' => 'gray',
                                'disabled' => 'warning',
                            ])
                            ->grouped()
                            ->required(),
                        ToggleButtons::make('dark_mode_forced')
                            ->label('Forcer le mode sombre')
                            ->options([
                                'disabled' => 'Non',
                                'enabled' => 'Oui',
                            ])
                            ->icons([
                                'disabled' => Heroicon::OutlinedComputerDesktop,
                                'enabled' => Heroicon::OutlinedMoon,
                            ])
                            ->colors([
                                'disabled' => 'gray',
                                'enabled' => 'info',
                            ])
                            ->grouped()
                            ->required(),
                        ToggleButtons::make('sidebar_width')
                            ->label('Largeur sidebar')
                            ->options([
                                '16rem' => 'Compacte',
                                '18rem' => 'Réduite',
                                '20rem' => 'Standard',
                                '22rem' => 'Large',
                                '24rem' => 'Très large',
                            ])
                            ->grouped()
                            ->required(),
                        ToggleButtons::make('collapsed_sidebar_width')
                            ->label('Sidebar repliée')
                            ->options([
                                '4rem' => 'Compacte',
                                '4.5rem' => 'Standard',
                                '5rem' => 'Large',
                            ])
                            ->grouped()
                            ->required(),
                    ])
                    ->columns([
                        'md' => 2,
                    ]),
                Section::make('Identité des rapports')
                    ->description('Ces valeurs alimentent l’en-tête et le pied de page des PDF générés.')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->schema([
                        TextInput::make('brand_name')
                            ->label('Nom affiché')
                            ->required()
                            ->maxLength(120),
                        FileUpload::make('header_image_path')
                            ->label('Image d’en-tête')
                            ->disk('public')
                            ->directory('report-branding')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg'])
                            ->helperText('Image JPEG utilisée comme en-tête fixe du rapport.')
                            ->columnSpanFull(),
                        FileUpload::make('footer_image_path')
                            ->label('Image de pied de page')
                            ->disk('public')
                            ->directory('report-branding')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg'])
                            ->helperText('Image JPEG utilisée comme pied de page fixe du rapport.')
                            ->columnSpanFull(),
                        TextInput::make('entity_name')
                            ->label('Entité')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('service_name')
                            ->label('Service')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('classification_label')
                            ->label('Classification')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('document_nature')
                            ->label('Nature du document')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('table_title')
                            ->label('Titre du tableau')
                            ->required()
                            ->maxLength(120),
                        Textarea::make('footer_label')
                            ->label('Pied de page')
                            ->required()
                            ->rows(2)
                            ->maxLength(180)
                            ->columnSpanFull(),
                    ])
                    ->columns([
                        'md' => 2,
                    ]),
            ])
            ->statePath('data');
    }

    public function save(AppThemeService $theme, ReportTheme $reportTheme): void
    {
        $state = $this->form->getState();

        $theme->saveTheme($state);
        $reportTheme->saveIdentity($state);

        Notification::make()
            ->title('Paramètres enregistrés')
            ->body('Les couleurs du panel et l’identité des rapports ont été mises à jour.')
            ->success()
            ->send();
    }

    private function applyPreset(?string $preset): void
    {
        $presets = [
            'amber' => [
                'primary' => '#f59e0b',
                'gray' => '#71717a',
                'success' => '#22c55e',
                'warning' => '#f59e0b',
                'danger' => '#ef4444',
                'info' => '#3b82f6',
            ],
            'blue' => [
                'primary' => '#2563eb',
                'gray' => '#64748b',
                'success' => '#16a34a',
                'warning' => '#d97706',
                'danger' => '#dc2626',
                'info' => '#0284c7',
            ],
            'green' => [
                'primary' => '#15803d',
                'gray' => '#64748b',
                'success' => '#16a34a',
                'warning' => '#ca8a04',
                'danger' => '#dc2626',
                'info' => '#0ea5e9',
            ],
            'slate' => [
                'primary' => '#334155',
                'gray' => '#475569',
                'success' => '#059669',
                'warning' => '#d97706',
                'danger' => '#e11d48',
                'info' => '#2563eb',
            ],
        ];

        if (! isset($presets[$preset])) {
            return;
        }

        $this->form->fill([
            ...$this->data,
            ...$presets[$preset],
            'preset' => $preset,
        ]);
    }
}
