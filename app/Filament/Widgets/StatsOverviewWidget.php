<?php

namespace App\Filament\Widgets;


use App\Models\Alerte;
use App\Models\Article;
use App\Models\Stock;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 10;

    // Rafraîchissement automatique toutes les 30 secondes
    protected  ?string $pollingInterval = '30s';

     protected function getStats(): array
    {
        $total       = Article::count();
        $actifs      = Article::where('is_archived', false)->count();
        $archives    = Article::where('is_archived', true)->count();

        // Stock disponible total (somme de toutes les lignes Disponible)
        $disponible  = Stock::where('statut', 'Disponible')->sum('quantite');
        $affecte     = Stock::where('statut', 'Affecté')->sum('quantite');
        $maintenance = Stock::where('statut', 'En_maintenance')->sum('quantite');
        $reforme     = Stock::where('statut', 'Réformé')->sum('quantite');

        // Articles dont le stock disponible <= seuil minimal
        $sousSeuilCount = Article::where('is_archived', false)
            ->whereNotNull('quantite_min')
            ->whereHas('stocks', fn ($q) =>
                $q->where('statut', 'Disponible')
                  ->whereColumn('stocks.quantite', '<=', 'articles.quantite_min')
            )->count();

        $alertesNT = Alerte::where('statut', 'Non_traité')->count();

        return [
            Stat::make('Total Articles', $total)
                ->description("{$actifs} actif(s) — {$archives} archivé(s)")
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),

            Stat::make('Stock Disponible', $disponible)
                ->description('Unités disponibles en dépôt')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Affectations actives', $affecte)
                ->description('Unités actuellement dans les salles')
                ->descriptionIcon('heroicon-m-arrow-right-circle')
                ->color('warning'),

            Stat::make('En Maintenance', $maintenance)
                ->description('Unités temporairement indisponibles')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('gray'),

            Stat::make('Réformés', $reforme)
                ->description('Unités hors service définitif')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Sous Seuil Minimal', $sousSeuilCount)
                ->description($alertesNT . ' alerte(s) non traitée(s)')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($sousSeuilCount > 0 ? 'danger' : 'success'),
        ];
    }
}