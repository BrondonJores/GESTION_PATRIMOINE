<?php
// app/Filament/Resources/Affectations/Schemas/AffectationForm.php

namespace App\Filament\Resources\Affectations\Schemas;

use App\Models\Article;
use App\Models\Bloc;
use App\Models\Consommable;
use App\Models\Salle;
use App\Models\Stock;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AffectationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ── Discriminant ───────────────────────────────────────
            Select::make('type')
                ->label('Type de ressource')
                ->options([
                    'article'      => 'Équipement (table, chaise, ordinateur...)',
                    'consommable'  => 'Consommable (marqueur, papier...)',
                ])
                ->required()
                ->default('article')
                ->live() // réactif — affiche/masque les champs selon le type
                ->helperText('Choisir le type détermine la logique d\'affectation.'),

            // ── Champ article (visible si type = article) ──────────
            Select::make('article_id')
                ->label('Article')
                ->options(function () {
                    // Uniquement les articles disponibles
                    return Article::where('statut', Article::DISPONIBLE)
                        ->orderBy('designation')
                        ->get()
                        ->mapWithKeys(fn ($a) => [
                            $a->id => "[{$a->numero_reference}] {$a->designation}"
                        ])
                        ->toArray();
                })
                ->searchable()
                ->required(fn ($get) => $get('type') === 'article')
                ->visible(fn ($get) => $get('type') === 'article')
                ->helperText('Seuls les articles disponibles sont listés.'),

            // ── Champ consommable (visible si type = consommable) ──
            Select::make('consommable_id')
                ->label('Consommable')
                ->options(function () {
                    return Consommable::where('quantite_stock', '>', 0)
                        ->orderBy('designation')
                        ->get()
                        ->mapWithKeys(fn ($c) => [
                            $c->id => "[{$c->reference}] {$c->designation} — stock: {$c->quantite_stock}"
                        ])
                        ->toArray();
                })
                ->searchable()
                ->required(fn ($get) => $get('type') === 'consommable')
                ->visible(fn ($get) => $get('type') === 'consommable')
                ->helperText('Seuls les consommables avec du stock sont listés.'),

            // ── Quantité (visible uniquement pour les consommables) ─
            TextInput::make('quantite')
                ->label('Quantité')
                ->numeric()
                ->minValue(1)
                ->default(1)
                ->required(fn ($get) => $get('type') === 'consommable')
                ->visible(fn ($get) => $get('type') === 'consommable')
                ->helperText('Pour un équipement, la quantité est toujours 1.'),

            // ── Destination ────────────────────────────────────────
            Select::make('bloc_id')
                ->label('Bloc')
                ->options(
                    Bloc::where('actif', true)
                        ->orderBy('nom_bloc')
                        ->pluck('nom_bloc', 'id')
                        ->toArray()
                )
                ->required()
                ->live(),

            Select::make('salle_id')
                ->label('Salle (optionnelle)')
                ->options(fn ($get) =>
                    $get('bloc_id')
                        ? Salle::where('bloc_id', $get('bloc_id'))
                            ->where('actif', true)
                            ->orderBy('nom_salle')
                            ->pluck('nom_salle', 'id')
                            ->toArray()
                        : []
                )
                ->placeholder('-- Tout le bloc --'),

            // ── Date ───────────────────────────────────────────────
            DatePicker::make('date_affectation')
                ->label("Date d'affectation")
                ->default(now())
                ->maxDate(now())
                ->minDate(fn () =>
                    auth()->user()?->hasRole('admin') ? null : now()
                )
                ->required(),

            Textarea::make('observations')
                ->label('Observations')
                ->rows(3),
        ]);
    }
}