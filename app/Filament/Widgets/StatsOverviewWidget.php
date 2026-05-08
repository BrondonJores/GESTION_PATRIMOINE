<?php

namespace App\Filament\Widgets;

use App\Models\Affectation;
use App\Models\Alerte;
use App\Models\Article;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 10;

    // Rafraîchissement automatique toutes les 30 secondes
    protected  ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $total        = Article::count();
        $disponibles  = Article::where('statut', 'Disponible')->count();
        $maintenance  = Article::where('statut', 'En_maintenance')->count();
        $reformes     = Article::where('statut', 'Réformé')->count();
        $sousSeuilMin = Article::whereNotNull('quantite_min')
                               ->whereColumn('quantite', '<=', 'quantite_min')
                               ->count();
        $alertesNT    = Alerte::where('statut', 'Non_traité')->count();
        $affectActives = Affectation::whereNull('date_recuperation')->count();

        return [
            Stat::make('Total Articles', $total)
                ->description('Dans le catalogue')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),

            Stat::make('Disponibles', $disponibles)
                ->description('En stock — prêts à être affectés')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Affectés', $affectActives)
                ->description('Affectations actives en cours')
                ->descriptionIcon('heroicon-m-arrow-right-circle')
                ->color('warning'),

            Stat::make('En Maintenance', $maintenance)
                ->description('Temporairement indisponibles')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('gray'),

            Stat::make('Réformés / Archivés', $reformes)
                ->description('Hors service définitivement')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Sous Seuil Minimal', $sousSeuilMin)
                ->description($alertesNT . ' alerte(s) non traitée(s)')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($sousSeuilMin > 0 ? 'danger' : 'success'),
        ];
    }
}