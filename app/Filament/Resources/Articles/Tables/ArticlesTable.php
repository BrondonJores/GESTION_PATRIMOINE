<?php
// app/Filament/Resources/Articles/Tables/ArticlesTable.php

namespace App\Filament\Resources\Articles\Tables;

use App\Models\Article;
use App\Models\Famille;
use App\Services\ArticleService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\Auth;


class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_reference')
                    ->label('N° Référence')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('designation')
                    ->label('Désignation')
                    ->searchable(),

                TextColumn::make('code_ancien')
                    ->label('Code ancien')
                    ->searchable(),

                TextColumn::make('categorie.famille.nom_famille')
                    ->label('Famille'),

                TextColumn::make('categorie.nom_categorie')
                    ->label('Catégorie')
                    ->searchable(),

                // Statut direct sur l'article — un seul état à la fois
                TextColumn::make('statut')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'Disponible'    => 'success',
                        'Affecté'       => 'warning',
                        'En_maintenance' => 'gray',
                        'Réformé'       => 'danger',
                        default         => 'gray',
                    }),
            ])

            ->filters([
                SelectFilter::make('statut')
                    ->options([
                        'Disponible'    => 'Disponible',
                        'Affecté'       => 'Affecté',
                        'En_maintenance' => 'En maintenance',
                        'Réformé'       => 'Réformé',
                    ]),

                SelectFilter::make('famille')
                    ->label('Famille')
                    ->options(Famille::pluck('nom_famille', 'id')->toArray())
                    ->query(
                        fn(Builder $q, array $data) =>
                        filled($data['value'])
                            ? $q->whereHas('categorie', fn($s) =>
                            $s->where('famille_id', $data['value']))
                            : $q
                    ),

                SelectFilter::make('categorie_id')
                    ->label('Catégorie')
                    ->relationship('categorie', 'nom_categorie'),
            ])

            ->recordActions([
                EditAction::make(),

                // ── MAINTENANCE ────────────────────────────────────
                Action::make('maintenance')
                    ->label('Maintenance')
                    ->icon('heroicon-m-wrench-screwdriver')
                    ->color('warning')
                    ->disabled(fn(Article $r) => !$r->estDisponible())
                    ->tooltip(
                        fn(Article $r) =>
                        !$r->estDisponible()
                            ? "Statut actuel : {$r->statut} — seul un article Disponible peut être mis en maintenance"
                            : 'Mettre en maintenance'
                    )
                    ->form([
                        Textarea::make('motif')
                            ->label('Motif de la maintenance')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (Article $record, array $data) {
                        try {
                            app(ArticleService::class)
                                ->mettreEnMaintenance($record, $data['motif']);
                            Notification::make()
                                ->title('Mise en maintenance enregistrée')
                                ->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')
                                ->body($e->getMessage())
                                ->danger()->send();
                        }
                    }),

                // ── RETOUR MAINTENANCE ─────────────────────────────
                Action::make('retour_maintenance')
                    ->label('Retour maintenance')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('info')
                    ->disabled(fn(Article $r) => !$r->estEnMaintenance())
                    ->tooltip(
                        fn(Article $r) =>
                        !$r->estEnMaintenance()
                            ? "Statut actuel : {$r->statut} — action non applicable"
                            : 'Remettre en stock disponible'
                    )
                    ->action(function (Article $record) {
                        try {
                            app(ArticleService::class)->retourMaintenance($record);
                            Notification::make()
                                ->title('Retour en service enregistré')
                                ->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')
                                ->body($e->getMessage())
                                ->danger()->send();
                        }
                    }),

                // RÉFORMER
                Action::make('reformer')
                    ->label('Réformer')
                    ->icon('heroicon-m-archive-box-x-mark')
                    ->color('danger')
                    ->disabled(fn(Article $r) => $r->estAffecte() || $r->estReforme())
                    ->tooltip(
                        fn(Article $r) =>
                        $r->estReforme()
                            ? 'Déjà réformé'
                            : ($r->estAffecte()
                                ? 'Récupérez l\'article avant de le réformer'
                                : 'Réformer définitivement cet article')
                    )
                    ->modalHeading('Réformer cet article')
                    ->modalSubmitActionLabel('Confirmer la réforme')
                    ->form([
                        Textarea::make('motif')
                            ->label('Motif de réforme')
                            ->required()
                            ->rows(2)
                            ->helperText('Cette action est irréversible sauf par l\'administrateur.'),
                    ])
                    ->action(function (Article $record, array $data) {
                        try {
                            app(ArticleService::class)->reformer($record, $data['motif']);
                            Notification::make()
                                ->title('Article réformé')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                // ── RÉINTÉGRER 
                Action::make('reintegrer')
                    ->label('Réintégrer')
                    ->icon('heroicon-m-arrow-path')
                    ->color('success')
                    ->disabled(
                        fn(Article $r) =>
                        !Auth::user()?->hasRole('admin') || !$r->estReforme()
                    )
                    ->tooltip(
                        fn(Article $r) =>
                        !Auth::user()?->hasRole('admin')
                            ? 'Action réservée à l\'administrateur'
                            : (!$r->estReforme()
                                ? "Statut actuel : {$r->statut} — seul un article Réformé peut être réintégré"
                                : 'Réintégrer dans le stock disponible')
                    )
                    ->modalHeading('Réintégrer cet article')
                    ->modalSubmitActionLabel('Confirmer la réintégration')
                    ->form([
                        Textarea::make('motif')
                            ->label('Motif de réintégration')
                            ->required()
                            ->rows(2)
                            ->helperText('L\'article repassera au statut Disponible.'),
                    ])
                    ->action(function (Article $record, array $data) {
                        try {
                            app(ArticleService::class)->reintegrer($record, $data['motif']);
                            Notification::make()
                                ->title('Article réintégré')
                                ->body('L\'article est de nouveau disponible.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actionsColumnLabel('Actions')
            ->toolbarActions([
                BulkActionGroup::make([]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
