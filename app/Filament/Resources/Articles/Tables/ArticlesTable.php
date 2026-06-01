<?php
// app/Filament/Resources/Articles/Tables/ArticlesTable.php

namespace App\Filament\Resources\Articles\Tables;

use App\Models\Article;
use App\Models\Famille;
use App\Services\ArticleImportService;
use App\Services\ArticleService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('qr_code')
                    ->label('QR')
                    ->getStateUsing(fn (Article $record) => app(ArticleService::class)->generateQrCode($record))
                    ->width(40)
                    ->height(40),

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
                EditAction::make()
                    ->visible(fn () =>
                        Auth::user()?->can('update articles') ?? false
                    ),

                Action::make('print_label')
                    ->label('Étiquette')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->action(function (Article $record) {
                        $qrCode = app(ArticleService::class)->generateQrCode($record);
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.article-label', [
                            'article' => $record,
                            'qrCode' => $qrCode,
                        ])->setPaper([0, 0, 170, 113]); // Environ 60mm x 40mm en points

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            "etiquette-{$record->numero_reference}.pdf"
                        );
                    }),

                // ── MAINTENANCE ────────────────────────────────────
                Action::make('maintenance')
                    ->label('Maintenance')
                    ->icon('heroicon-m-wrench-screwdriver')
                    ->color('warning')
                    ->visible(fn () =>
                        Auth::user()?->hasAnyRole(['admin', 'gestionnaire']) ?? false
                    )
                    ->disabled(fn (Article $r) => !$r->estDisponible())
                    ->tooltip(fn (Article $r) =>
                        !$r->estDisponible()
                            ? "Statut actuel : {$r->statut} — seul un article Disponible peut être mis en maintenance"
                            : 'Mettre en maintenance'
                    )
                    ->form([
                        Textarea::make('motif')
                            ->label('Motif de la maintenance')
                            ->required()->rows(2),
                    ])
                    ->action(function (Article $record, array $data) {
                        try {
                            app(ArticleService::class)->mettreEnMaintenance($record, $data['motif']);
                            Notification::make()->title('Mise en maintenance enregistrée')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // ── RETOUR MAINTENANCE ─────────────────────────────
                Action::make('retour_maintenance')
                    ->label('Retour maintenance')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('info')
                    ->visible(fn () =>
                        Auth::user()?->hasAnyRole(['admin', 'gestionnaire']) ?? false
                    )
                    ->disabled(fn (Article $r) => !$r->estEnMaintenance())
                    ->tooltip(fn (Article $r) =>
                        !$r->estEnMaintenance()
                            ? "Statut actuel : {$r->statut} — action non applicable"
                            : 'Remettre en stock disponible'
                    )
                    ->action(function (Article $record) {
                        try {
                            app(ArticleService::class)->retourMaintenance($record);
                            Notification::make()->title('Retour en service enregistré')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // ── RÉFORMER ───────────────────────────────────────
                // Admin uniquement
                Action::make('reformer')
                    ->label('Réformer')
                    ->icon('heroicon-m-archive-box-x-mark')
                    ->color('danger')
                    ->visible(fn () =>
                        Auth::user()?->hasRole('admin') ?? false
                    )
                    ->disabled(fn (Article $r) => $r->estAffecte() || $r->estReforme())
                    ->tooltip(fn (Article $r) =>
                        $r->estReforme()
                            ? 'Déjà réformé'
                            : ($r->estAffecte()
                                ? 'Récupérez l\'article avant de le réformer'
                                : 'Réformer définitivement cet article')
                    )
                    ->modalHeading('Réformer cet article')
                    ->modalDescription('Action irréversible sauf par l\'administrateur.')
                    ->modalSubmitActionLabel('Confirmer la réforme')
                    ->form([
                        Textarea::make('motif')
                            ->label('Motif de réforme')
                            ->required()->rows(2),
                    ])
                    ->action(function (Article $record, array $data) {
                        try {
                            app(ArticleService::class)->reformer($record, $data['motif']);
                            Notification::make()->title('Article réformé')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // ── RÉINTÉGRER ─────────────────────────────────────
                // Admin uniquement
                Action::make('reintegrer')
                    ->label('Réintégrer')
                    ->icon('heroicon-m-arrow-path')
                    ->color('success')
                    ->visible(fn () =>
                        Auth::user()?->hasRole('admin') ?? false
                    )
                    ->disabled(fn (Article $r) => !$r->estReforme())
                    ->tooltip(fn (Article $r) =>
                        !$r->estReforme()
                            ? "Statut actuel : {$r->statut} — seul un article Réformé peut être réintégré"
                            : 'Réintégrer dans le stock disponible'
                    )
                    ->modalHeading('Réintégrer cet article')
                    ->modalDescription('L\'article repassera au statut Disponible.')
                    ->modalSubmitActionLabel('Confirmer la réintégration')
                    ->form([
                        Textarea::make('motif')
                            ->label('Motif de réintégration')
                            ->required()->rows(2),
                    ])
                    ->action(function (Article $record, array $data) {
                        try {
                            app(ArticleService::class)->reintegrer($record, $data['motif']);
                            Notification::make()->title('Article réintégré')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->actionsColumnLabel('Actions')
            ->toolbarActions([
                Action::make('telecharger_canevas')
                    ->label('Canevas CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn () => response()->streamDownload(
                        fn () => print app(ArticleImportService::class)->csvTemplate(),
                        'canevas-articles.csv',
                        ['Content-Type' => 'text/csv; charset=UTF-8'],
                    )),
                Action::make('importer')
                    ->label('Importer')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->visible(fn () => Auth::user()?->can('create articles') ?? false)
                    ->form([
                        FileUpload::make('fichier')
                            ->label('Fichier CSV')
                            ->disk('local')
                            ->directory('imports/articles')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $path = is_array($data['fichier']) ? reset($data['fichier']) : $data['fichier'];
                        $result = app(ArticleImportService::class)->importCsv(Storage::disk('local')->path($path));
                        Storage::disk('local')->delete($path);

                        $body = "{$result['created']} créé(s), {$result['updated']} mis à jour, {$result['skipped']} ignoré(s).";

                        if ($result['errors'] !== []) {
                            $body .= "\n" . implode("\n", array_slice($result['errors'], 0, 5));
                        }

                        Notification::make()
                            ->title('Import terminé')
                            ->body($body)
                            ->success()
                            ->send();
                    }),
                BulkActionGroup::make([
                    BulkAction::make('print_labels')
                        ->label('Imprimer Étiquettes')
                        ->icon('heroicon-o-printer')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $pdfContent = '';
                            foreach ($records as $record) {
                                $qrCode = app(ArticleService::class)->generateQrCode($record);
                                $pdfContent .= \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.article-label', [
                                    'article' => $record,
                                    'qrCode' => $qrCode,
                                ])->setPaper([0, 0, 170, 113])->output();
                            }

                            // Note: Concatenating PDF outputs directly like this might not work perfectly with all PDF engines.
                            // A better way is to use a single view with a loop and page breaks.
                            
                            return response()->streamDownload(
                                function () use ($records) {
                                    $data = [];
                                    foreach ($records as $record) {
                                        $data[] = [
                                            'article' => $record,
                                            'qrCode' => app(ArticleService::class)->generateQrCode($record),
                                        ];
                                    }
                                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.article-labels-bulk', [
                                        'labels' => $data,
                                    ])->setPaper([0, 0, 170, 113]);
                                    print($pdf->output());
                                },
                                "etiquettes-bulk.pdf"
                            );
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
