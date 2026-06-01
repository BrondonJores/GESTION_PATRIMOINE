<?php

namespace App\Filament\Resources\Affectations\Schemas;

use App\Models\Article;
use App\Models\Bloc;
use App\Models\Consommable;
use App\Models\Salle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AffectationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Type de ressource')
                    ->required()
                    ->options([
                        'article'     => 'Équipement (table, chaise, ordinateur...)',
                        'consommable' => 'Consommable (marqueur, papier...)',
                    ])
                    ->default('article')
                    ->helperText('Choisir le type détermine la logique d\'affectation.')
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('article_id', null);
                        $set('consommable_id', null);
                        $set('quantite', 1);
                    }),

                // ── Équipement ──
               // ── Équipement ──
                Select::make('article_ids')
                    ->label('Articles')
                    ->required(fn(Get $get) => $get('type') === 'article')
                    ->visible(fn(Get $get) => $get('type') === 'article')
                    ->options(fn() => Article::where('statut', Article::DISPONIBLE)
                    ->pluck('designation', 'id'))
                    ->multiple()
                    ->searchable()
                    ->helperText('Sélectionnez un ou plusieurs articles disponibles.')
                     ->placeholder('Sélectionnez une option'),

                // ── Consommable ──
                Select::make('consommable_id')
                    ->label('Consommable')
                    ->required(fn(Get $get) => $get('type') === 'consommable')
                    ->visible(fn(Get $get) => $get('type') === 'consommable')
                    ->options(fn() => Consommable::where('quantite_stock', '>', 0)
                        ->pluck('designation', 'id'))
                    ->searchable()
                    ->helperText('Seuls les consommables avec du stock sont listés.')
                    ->placeholder('Sélectionnez une option'),

                TextInput::make('quantite')
                    ->label('Quantité')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->helperText('Pour un consommable, indiquez la quantité souhaitée.')
                    ->visible(fn(Get $get) => $get('type') === 'consommable'),

                Select::make('bloc_id')
                    ->label('Bloc')
                    ->required()
                    ->options(fn() => Bloc::where('actif', true)->pluck('nom_bloc', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn(Set $set) => $set('salle_id', null))
                    ->placeholder('Sélectionnez une option'),

               
           Select::make('salle_id')
    ->label('Salle (optionnelle)')
    ->options(fn(Get $get) => $get('bloc_id')
        ? Salle::where('bloc_id', $get('bloc_id'))
            ->where('actif', true)
            ->pluck('nom_salle', 'id')
        : [])
    ->searchable()
    ->placeholder('-- Tout le bloc --'),
                DatePicker::make('date_affectation')
                    ->label('Date d\'affectation')
                    ->required()
                    ->default(now())
                    ->displayFormat('d/m/Y')
                    ->maxDate(now())
                    ->minDate(fn() => auth()->user()?->hasRole('admin') 
                     ? null 
                    : now()->toDateString()
                     ),

                Textarea::make('observations')
                    ->label('Observations')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }
}