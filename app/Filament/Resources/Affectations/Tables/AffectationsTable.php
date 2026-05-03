<?php

namespace App\Filament\Resources\Affectations\Tables;

use App\Models\Bloc;
use App\Models\Salle;
use App\Services\AffectationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AffectationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('article.designation')
                    ->label('Article')
                    ->searchable(),

                TextColumn::make('article.numero_reference')
                    ->label('Référence')
                    ->searchable(),

                TextColumn::make('bloc.nom_bloc')
                    ->label('Bloc'),

                TextColumn::make('salle.nom_salle')
                    ->label('Salle')
                    ->placeholder('Tout le bloc'),

                TextColumn::make('quantite')
                    ->label('Quantité'),

                TextColumn::make('date_affectation')
                    ->label('Date affectation')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('date_recuperation')
                    ->label('Statut')
                    ->date('d/m/Y')
                    ->placeholder('Active')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Responsable'),
            ])
            ->filters([
                Filter::make('actives')
                    ->label('Actives uniquement')
                    ->query(fn (Builder $query) => $query->whereNull('date_recuperation'))
                    ->toggle(),

                SelectFilter::make('bloc_id')
                    ->label('Bloc')
                    ->options(Bloc::pluck('nom_bloc', 'id')->toArray()),

                SelectFilter::make('article_id')
                    ->label('Article')
                    ->relationship('article', 'designation'),
            ])
            ->recordActions([
                // ── RÉAFFECTER ──────────────────────────────────────
                Action::make('reaffecter')
                    ->label('Réaffecter')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->modal()
                    ->modalHeading('Réaffecter vers un autre bloc/salle')
                    ->form([
                        Select::make('bloc_id')
                            ->label('Nouveau bloc')
                            ->options(Bloc::where('actif', true)->pluck('nom_bloc', 'id')->toArray())
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Select $component) => $component
                                ->getContainer()
                                ->getComponent('salle_id')
                                ?->state(null)
                            ),

                        Select::make('salle_id')
                            ->label('Nouvelle salle (optionnelle)')
                            ->options(function ($get) {
                                $blocId = $get('bloc_id');
                                if (!$blocId) return [];
                                return Salle::where('bloc_id', $blocId)
                                    ->where('actif', true)
                                    ->pluck('nom_salle', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->placeholder('-- Tout le bloc --'),

                        TextInput::make('quantite')
                            ->label('Quantité à réaffecter')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        Textarea::make('observations')
                            ->label('Observations')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        app(AffectationService::class)->reaffecter($record, $data);
                    })
                    ->visible(fn ($record) => is_null($record->date_recuperation)),

                // ── RÉCUPÉRER ────────────────────────────────────────
                Action::make('recuperer')
                    ->label('Récupérer')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->modal()
                    ->modalHeading('Récupérer au stock')
                    ->form([
                        TextInput::make('quantite')
                            ->label('Quantité à récupérer')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        DatePicker::make('date_recuperation')
                            ->label('Date de récupération')
                            ->default(now())
                            ->required(),

                        Textarea::make('observations')
                            ->label('Observations')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        app(AffectationService::class)->recuperer($record, $data);
                    })
                    ->visible(fn ($record) => is_null($record->date_recuperation)),

                // ── EDIT / DELETE ────────────────────────────────────
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->actionsColumnLabel('Actions')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}