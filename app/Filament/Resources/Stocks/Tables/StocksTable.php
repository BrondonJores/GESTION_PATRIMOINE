<?php

namespace App\Filament\Resources\Stocks\Tables;

use App\Models\Article;
use App\Models\Famille;
use App\Models\Stock;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            //La requête porte sur Article — une ligne = un article
            ->query(Article::query()->with(['categorie.famille', 'stocks']))
            ->columns([
                TextColumn::make('numero_reference')
                    ->label('N° Référence')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('designation')
                    ->label('Désignation')
                    ->searchable(),

                TextColumn::make('categorie.nom_categorie')
                    ->label('Catégorie')
                    ->searchable(),


                TextColumn::make('disponible')
                    ->label('Disponible')
                    ->getStateUsing(fn (Article $r) =>
                        Stock::quantitePour($r->id, Stock::DISPONIBLE))
                    ->badge()
                    ->color('success'),


                TextColumn::make('affecte')
                    ->label('Affecté')
                    ->getStateUsing(fn (Article $r) =>
                        Stock::quantitePour($r->id, Stock::AFFECTE))
                    ->badge()
                    ->color('warning'),

                TextColumn::make('en_maintenance')
                    ->label('Maintenance')
                    ->getStateUsing(fn (Article $r) =>
                        Stock::quantitePour($r->id, Stock::MAINTENANCE))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('reforme')
                    ->label('Réformé')
                    ->getStateUsing(fn (Article $r) =>
                        Stock::quantitePour($r->id, Stock::REFORME))
                    ->badge()
                    ->color('danger'),          
            ])

            ->filters([
             // Filtre par Catégorie
                SelectFilter::make('categorie_id')
                    ->label('Catégorie')
                    ->relationship('categorie', 'nom_categorie'),

            ])

            ->recordActions([
                // ── Mise en maintenance
                // Désactivé si : archivé OU aucun stock disponible
                Action::make('maintenance')
                    ->label('Maintenance')
                    ->icon('heroicon-m-wrench-screwdriver')
                    ->color('warning')
                       ->disabled(fn (Article $r) =>
                        $r->is_archived ||
                        Stock::quantitePour($r->id, Stock::DISPONIBLE) === 0
                    )
                    ->tooltip(fn (Article $r) =>
                        $r->is_archived
                            ? 'Article archivé — aucune action possible'
                            : (Stock::quantitePour($r->id, Stock::DISPONIBLE) === 0
                                ? 'Aucun stock disponible à mettre en maintenance'
                                : null)
                    )
                    ->form([
                        TextInput::make('quantite')
                            ->label('Quantité à mettre en maintenance')
                            ->numeric()->minValue(1)->required(),
                    ])
                    ->action(function (Article $record, array $data) {
                        try {
                            app(StockService::class)
                                ->mettreEnMaintenance($record, (int) $data['quantite']);
                            Notification::make()
                                ->title('Mise en maintenance enregistrée')
                                ->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')->body($e->getMessage())
                                ->danger()->send();
                        }
                    }),

                // ── Retour de maintenance
                 // Désactivé si : aucun stock en maintenance
                Action::make('retour_maintenance')
                    ->label('Retour maintenance')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('info')
                     ->disabled(fn (Article $r) =>
                        Stock::quantitePour($r->id, Stock::MAINTENANCE) === 0
                    )
                    ->tooltip(fn (Article $r) =>
                        Stock::quantitePour($r->id, Stock::MAINTENANCE) === 0
                            ? 'Aucun article en maintenance pour cet équipement'
                            : null
                    )
                    ->form([
                        TextInput::make('quantite')
                            ->label('Quantité à remettre en service')
                            ->numeric()->minValue(1)->required(),
                    ])
                    ->action(function (Article $record, array $data) {
                        try {
                            app(StockService::class)
                                ->remettreEnService($record, (int) $data['quantite']);
                            Notification::make()
                                ->title('Remise en service enregistrée')
                                ->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')->body($e->getMessage())
                                ->danger()->send();
                        }
                    }),

                // ── Réforme partielle
                // Désactivé si : article déjà archivé &  ET si disponible= 0 ET maintenance=0 → rien à réformer
                Action::make('reformer')
                    ->label('Réformer')
                    ->icon('heroicon-m-archive-box-x-mark')
                    ->color('danger')
                     ->disabled(fn (Article $r) =>
                        $r->is_archived ||
                        (Stock::quantitePour($r->id, Stock::DISPONIBLE) === 0 &&
                         Stock::quantitePour($r->id, Stock::MAINTENANCE) === 0)
                    )
                    ->tooltip(fn (Article $r) =>
                        $r->is_archived
                            ? 'Article archivé — aucune action possible'
                            : (Stock::quantitePour($r->id, Stock::DISPONIBLE) === 0 &&
                               Stock::quantitePour($r->id, Stock::MAINTENANCE) === 0
                                ? 'Aucun stock disponible ou en maintenance à réformer'
                                : null)
                    )
                    ->form([
                        Select::make('statut_source')
                            ->label('Réformer depuis')
                            ->options([
                                Stock::DISPONIBLE  => 'Disponible',
                                Stock::MAINTENANCE => 'En maintenance',
                            ])
                            ->required(),
                        TextInput::make('quantite')
                            ->label('Quantité à réformer')
                            ->numeric()->minValue(1)->required(),
                        Textarea::make('motif')
                            ->label('Motif de réforme')
                            ->required()->rows(2),
                    ])
                    ->action(function (Article $record, array $data) {
                        try {
                            app(StockService::class)->reformer(
                                $record,
                                $data['statut_source'],
                                (int) $data['quantite'],
                                $data['motif']
                            );
                            Notification::make()
                                ->title('Réforme enregistrée')
                                ->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')->body($e->getMessage())
                                ->danger()->send();
                        }
                    }),

                // ── RÉINTÉGRER
                // Réformé → Disponible
                // Désactivé si : aucun stock réformé
                Action::make('reintegrer')
                    ->label('Réintégrer')
                    ->icon('heroicon-m-arrow-path')
                    ->color('success')
                    ->disabled(fn (Article $r) =>
                        Stock::quantitePour($r->id, Stock::REFORME) === 0
                    )
                    ->tooltip(fn (Article $r) =>
                        Stock::quantitePour($r->id, Stock::REFORME) === 0
                            ? 'Aucune unité réformée à réintégrer'
                            : 'Remettre des unités réformées en stock disponible'
                    )
                    ->form([
                        TextInput::make('quantite')
                            ->label('Quantité à réintégrer dans le stock disponible')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->helperText('Ces unités passeront de Réformé → Disponible.'),
                        Textarea::make('motif')
                            ->label('Motif de réintégration')
                            ->required()
                            ->rows(2)
                            ->helperText('Ex: erreur de réforme, article réparé...'),
                    ])
                    ->action(function (Article $record, array $data) {
                        try {
                            app(StockService::class)->reintegrer(
                                $record,
                                (int) $data['quantite'],
                                $data['motif']
                            );
                            Notification::make()
                                ->title('Réintégration enregistrée')
                                ->body($data['quantite'] . ' unité(s) remises en stock disponible.')
                                ->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')->body($e->getMessage())
                                ->danger()->send();
                        }
                    }),
                // ── Archiver
                // Désactivé si : déjà archivé OU des unités encore affectées
                Action::make('archiver')
                    ->label('Archiver')
                    ->icon('heroicon-m-archive-box')
                    ->color('gray')
                    ->disabled(fn (Article $r) =>
                        $r->is_archived ||
                        Stock::quantitePour($r->id, Stock::AFFECTE) > 0
                    )
                    ->tooltip(fn (Article $r) =>
                        $r->is_archived
                            ? 'Cet article est déjà archivé'
                            : (Stock::quantitePour($r->id, Stock::AFFECTE) > 0
                                ? 'Impossible d\'archiver : des unités sont encore affectées dans des salles'
                                : 'Archiver définitivement cet article')
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Archiver l\'article')
                    ->modalDescription(
                        'Tout le stock disponible et en maintenance sera réformé. ' .
                        'Condition : aucune unité ne doit être affectée.'
                    )
                    ->modalSubmitActionLabel('Confirmer l\'archivage')
                    ->action(function (Article $record) {
                        try {
                            app(StockService::class)->archiverManuellement($record);
                            Notification::make()
                                ->title('Article archivé')
                                ->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')->body($e->getMessage())
                                ->danger()->send();
                        }
                    }),
            ])
            ->actionsColumnLabel('Actions')
            ->defaultSort('designation');
    }
}