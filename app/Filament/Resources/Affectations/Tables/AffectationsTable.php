<?php
// app/Filament/Resources/Affectations/Tables/AffectationsTable.php

namespace App\Filament\Resources\Affectations\Tables;

use App\Models\Affectation;
use App\Models\Bloc;
use App\Models\Salle;
use App\Services\AffectationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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
                // Type de ressource
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'article'     => 'primary',
                        'consommable' => 'warning',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'article'     => 'Équipement',
                        'consommable' => 'Consommable',
                        default       => $state,
                    }),

                // Désignation — article ou consommable selon le type
                TextColumn::make('label')
                    ->label('Ressource')
                    ->getStateUsing(fn (Affectation $r) => $r->label)
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHas('article', fn ($q) =>
                            $q->where('designation', 'like', "%{$search}%")
                              ->orWhere('numero_reference', 'like', "%{$search}%")
                        )->orWhereHas('consommable', fn ($q) =>
                            $q->where('designation', 'like', "%{$search}%")
                        );
                    }),

                // Référence
                TextColumn::make('reference')
                    ->label('Référence')
                    ->getStateUsing(fn (Affectation $r) => $r->reference),

                TextColumn::make('bloc.nom_bloc')
                    ->label('Bloc'),

                TextColumn::make('salle.nom_salle')
                    ->label('Salle')
                    ->placeholder('Tout le bloc'),

                TextColumn::make('quantite')
                    ->label('Qté'),

                TextColumn::make('date_affectation')
                    ->label('Date affectation')
                    ->date('d/m/Y')
                    ->sortable(),

                // Statut : active ou clôturée
                TextColumn::make('statut_affectation')
                    ->label('Statut')
                    ->getStateUsing(fn (Affectation $r) =>
                        $r->estActive() ? 'Active' : 'Clôturée'
                    )
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Active'    => 'success',
                        'Clôturée'  => 'gray',
                        default     => 'gray',
                    }),

                TextColumn::make('user.name')
                    ->label('Responsable'),
            ])

            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'article'     => 'Équipements',
                        'consommable' => 'Consommables',
                    ]),

                Filter::make('actives')
                    ->label('Actives uniquement')
                    ->query(fn (Builder $q) =>
                        $q->whereNull('date_recuperation')
                    )
                    ->toggle(),

                SelectFilter::make('bloc_id')
                    ->label('Bloc')
                    ->options(Bloc::pluck('nom_bloc', 'id')->toArray()),
            ])

            ->recordActions([
                // ── RÉCUPÉRER (articles uniquement) ───────────────
                Action::make('recuperer')
                    ->label('Récupérer')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn (Affectation $r) =>
                        $r->estPourArticle() && $r->estActive()
                    )
                    ->form([
                        DatePicker::make('date_recuperation')
                            ->label('Date de récupération')
                            ->default(now())
                            ->maxDate(now())
                            ->required(),

                        Textarea::make('observations')
                            ->label('Observations')
                            ->rows(2),
                    ])
                    ->action(function (Affectation $record, array $data) {
                        try {
                            app(AffectationService::class)->recuperer($record, $data);
                            Notification::make()
                                ->title('Récupération enregistrée')
                                ->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')
                                ->body($e->getMessage())
                                ->danger()->send();
                        }
                    }),

                // ── RÉAFFECTER (articles uniquement) ──────────────
                Action::make('reaffecter')
                    ->label('Réaffecter')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->visible(fn (Affectation $r) =>
                        $r->estPourArticle() && $r->estActive()
                    )
                    ->form([
                        Select::make('bloc_id')
                            ->label('Nouveau bloc')
                            ->options(
                                Bloc::where('actif', true)->pluck('nom_bloc', 'id')
                            )
                            ->required()
                            ->live(),

                        Select::make('salle_id')
                            ->label('Nouvelle salle (optionnelle)')
                            ->options(fn ($get) =>
                                $get('bloc_id')
                                    ? Salle::where('bloc_id', $get('bloc_id'))
                                        ->where('actif', true)
                                        ->pluck('nom_salle', 'id')
                                        ->toArray()
                                    : []
                            )
                            ->placeholder('-- Tout le bloc --'),

                        Textarea::make('observations')
                            ->label('Observations')
                            ->rows(2),
                    ])
                    ->action(function (Affectation $record, array $data) {
                        try {
                            app(AffectationService::class)->reaffecter($record, $data);
                            Notification::make()
                                ->title('Réaffectation enregistrée')
                                ->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')
                                ->body($e->getMessage())
                                ->danger()->send();
                        }
                    }),
            ])
            ->actionsColumnLabel('Actions')
            ->toolbarActions([
                BulkActionGroup::make([]),
            ])
            ->defaultSort('date_affectation', 'desc');
    }
}