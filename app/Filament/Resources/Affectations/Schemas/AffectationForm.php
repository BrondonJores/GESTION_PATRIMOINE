<?php

namespace App\Filament\Resources\Affectations\Schemas;

use App\Models\Article;
use App\Models\Bloc;
use App\Models\Stock;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class AffectationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('article_id')
                ->label('Article')
                ->options(function () {
                    return Article::where('is_archived', false)
                        ->get()
                        ->filter(fn ($a) =>
                            Stock::quantitePour($a->id, Stock::DISPONIBLE) > 0
                        )
                        ->pluck('designation', 'id')
                        ->toArray();
                })
                ->searchable()
                ->required()
                ->helperText('Seuls les articles avec du stock disponible sont listés.'),

            Select::make('bloc_id')
                ->label('Bloc')
                ->options(
                    Bloc::where('actif', true)
                        ->pluck('nom_bloc', 'id')
                        ->toArray()
                )
                ->searchable()
                ->required()
                ->live(),

            Select::make('salle_id')
                ->label('Salle (optionnelle)')
                ->relationship(
                    name: 'salle',
                    titleAttribute: 'nom_salle',
                    modifyQueryUsing: fn (Builder $query, $get) => $query
                        ->where('actif', true)
                        ->when(
                            $get('bloc_id'),
                            fn ($q, $blocId) => $q->where('bloc_id', $blocId)
                        )
                )
                ->searchable()
                ->preload()
                ->placeholder('-- Tout le bloc --'),

            TextInput::make('quantite')
                ->label('Quantité')
                ->numeric()
                ->minValue(1)
                ->required(),

DatePicker::make('date_affectation')
    ->label("Date d'affectation")
    ->default(now())
    ->required()
    // Tout le monde : pas de date future autorisée
    ->maxDate(now())
    // Gestionnaire et utilisateur : seulement aujourd'hui
    // Admin : peut choisir n'importe quelle date passée (minDate libre)
    ->minDate(function () {
        return auth()->user()?->hasRole('admin')
            ? null       // admin : pas de limite dans le passé
            : now();     // autres : seulement aujourd'hui
    }),
            Textarea::make('observations')
                ->label('Observations')
                ->rows(3),
        ]);
    }
}