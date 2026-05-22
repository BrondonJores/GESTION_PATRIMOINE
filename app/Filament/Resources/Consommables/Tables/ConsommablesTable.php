<?php

namespace App\Filament\Resources\Consommables\Tables;

use App\Models\Consommable;
use App\Services\ConsommableImportService;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ConsommablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->placeholder('—'),
             
                TextColumn::make('designation')
                    ->label('Désignation')
                    ->searchable(),

                TextColumn::make('categorie.nom_categorie')
                    ->label('Catégorie'),

                TextColumn::make('quantite_stock')
                    ->label('Stock actuel')
                    ->badge()
                    ->color(fn (Consommable $r) => match (true) {
                        $r->quantite_stock <= 0 => 'danger',
                        $r->quantite_stock <= $r->quantite_min => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('quantite_min')
                    ->label('Seuil minimal')
                    ->placeholder('—')
                    ->sortable(),
                    
                TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Disponible' => 'success',
                        'Sous seuil' => 'warning',
                        'Épuisé' => 'danger',
                        default => 'gray',
                    }),
            ])

            ->filters([
                SelectFilter::make('statut')
                    ->options([
                        'Disponible' => 'Disponible',
                        'Sous seuil' => 'Sous seuil',
                        'Épuisé' => 'Épuisé',
                    ]),

                SelectFilter::make('categorie_id')
                    ->relationship('categorie', 'nom_categorie'),
            ])

            ->recordActions([
                EditAction::make(),

                Action::make('reapprovisionner')
                    ->label('Réapprovisionner')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('quantite')
                            ->numeric()
                            ->required(),

                        \Filament\Forms\Components\Textarea::make('motif')
                            ->required(),
                    ])
                    ->action(function (Consommable $record, array $data) {
                        $record->increment('quantite_stock', $data['quantite']);

                        Notification::make()
                            ->title('Stock mis à jour')
                            ->success()
                            ->send();
                    }),
            ])
            ->actionsColumnLabel('Actions')
            ->toolbarActions([
                Action::make('telecharger_canevas')
                    ->label('Canevas CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn () => response()->streamDownload(
                        fn () => print app(ConsommableImportService::class)->csvTemplate(),
                        'canevas-consommables.csv',
                        ['Content-Type' => 'text/csv; charset=UTF-8'],
                    )),
                Action::make('importer')
                    ->label('Importer')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->form([
                        FileUpload::make('fichier')
                            ->label('Fichier CSV')
                            ->disk('local')
                            ->directory('imports/consommables')
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $path = is_array($data['fichier']) ? reset($data['fichier']) : $data['fichier'];
                        $result = app(ConsommableImportService::class)->importCsv(Storage::disk('local')->path($path));
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
                BulkActionGroup::make([]),
            ]);
    }
}
