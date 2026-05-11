<?php

namespace App\Filament\Resources\Affectations\Schemas;

use App\Models\Article;
use App\Models\Bloc;
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
                ->options(
                    Article::whereNotIn('statut', ['Réformé', 'En_maintenance'])
                        ->pluck('designation', 'id')
                        ->toArray()
                )
                ->searchable()
                ->required(),

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
                // pas de ->required() → salle optionnelle

            TextInput::make('quantite')
                ->label('Quantité')
                ->numeric()
                ->minValue(1)
                ->required(),

            DatePicker::make('date_affectation')
                ->label("Date d'affectation")
                ->default(now())
                ->maxDate(now())
                ->required(),

            Textarea::make('observations')
                ->label('Observations')
                ->rows(3),
        ]);
    }
}