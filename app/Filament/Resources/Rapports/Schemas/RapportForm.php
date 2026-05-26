<?php

namespace App\Filament\Resources\Rapports\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class RapportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Paramètres du rapport')
                    ->schema([
                        Select::make('type_rapport')
                            ->label('Type de rapport')
                            ->options([
                                'Inventaire des articles' => 'Inventaire des articles',
                                'Inventaire des consommables' => 'Inventaire des consommables',
                                'Rapport par bloc' => 'Rapport par bloc',
                                'Rapport par salle' => 'Rapport par salle',
                                'Affectations' => 'Affectations',
                                'Réaffectations' => 'Réaffectations',
                                'Récupérations' => 'Récupérations',
                                'Alertes' => 'Alertes',
                                'Notifications' => 'Notifications',
                                'Utilisateurs' => 'Utilisateurs',
                                'Logs' => 'Logs',
                            ])
                            ->native(false)
                            ->searchable()
                            ->selectablePlaceholder(false)
                            ->required(),
                        Select::make('format')
                            ->label('Format')
                            ->options([
                                'PDF' => 'PDF',
                                'Excel' => 'Excel',
                            ])
                            ->native(false)
                            ->selectablePlaceholder(false)
                            ->default('PDF')
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Période du rapport')
                    ->description('La période limite les données exportées et rend le rapport plus ciblé.')
                    ->schema([
                        Select::make('periode_rapide')
                            ->label('Période rapide')
                            ->options([
                                'aujourdhui' => "Aujourd'hui",
                                'sept_derniers_jours' => '7 derniers jours',
                                'trente_derniers_jours' => '30 derniers jours',
                                'mois_en_cours' => 'Mois en cours',
                                'annee_en_cours' => 'Année en cours',
                                'personnalisee' => 'Période personnalisée',
                            ])
                            ->native(false)
                            ->default('trente_derniers_jours')
                            ->live()
                            ->afterStateUpdated(fn (?string $state, Set $set): mixed => self::appliquerPeriodeRapide($state, $set))
                            ->dehydrated(false)
                            ->required(),
                        DateTimePicker::make('periode_debut')
                            ->label('Début')
                            ->seconds(false)
                            ->beforeOrEqual('periode_fin')
                            ->required(),
                        DateTimePicker::make('periode_fin')
                            ->label('Fin')
                            ->seconds(false)
                            ->afterOrEqual('periode_debut')
                            ->beforeOrEqual('now')
                            ->required(),
                    ])
                    ->columns(3),
                Section::make('Informations système')
                    ->description('Ces informations sont générées automatiquement pour éviter les modifications manuelles.')
                    ->schema([
                        TextInput::make('chemin_fichier')
                            ->label('Chemin du fichier')
                            ->disabled()
                            ->dehydrated(false)
                            ->maxLength(255),
                        Select::make('user_id')
                            ->label('Généré par')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated(false),
                        DateTimePicker::make('date_generation')
                            ->label('Date de génération')
                            ->seconds(false)
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(3),
            ]);
    }

    private static function appliquerPeriodeRapide(?string $periode, Set $set): void
    {
        $maintenant = now();

        [$debut, $fin] = match ($periode) {
            'aujourdhui' => [$maintenant->copy()->startOfDay(), $maintenant],
            'sept_derniers_jours' => [$maintenant->copy()->subDays(6)->startOfDay(), $maintenant],
            'trente_derniers_jours' => [$maintenant->copy()->subDays(29)->startOfDay(), $maintenant],
            'mois_en_cours' => [$maintenant->copy()->startOfMonth(), $maintenant],
            'annee_en_cours' => [$maintenant->copy()->startOfYear(), $maintenant],
            default => [null, null],
        };

        if ($debut === null || $fin === null) {
            return;
        }

        $set('periode_debut', $debut);
        $set('periode_fin', $fin);
    }
}
