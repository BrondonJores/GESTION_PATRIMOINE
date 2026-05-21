<?php

namespace App\Filament\Widgets;



use App\Models\Article;
use App\Models\Consommable;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
      protected  ?string $heading = '';
    protected static ?int $sort = 10;

    // Rafraîchissement automatique toutes les 30 secondes
    protected  ?string $pollingInterval = '30s';

      
    protected function getStats(): array
    {
        //  ÉQUIPEMENTS 
        $totalEquipements = Article::count();
        $reformes         = Article::where('statut', Article::REFORME)->count();
        $totalActifs      = $totalEquipements - $reformes;

        //  CONSOMMABLES
        $consoTotal     = Consommable::count();
        $consoSousSeuil = Consommable::where('statut', 'Sous seuil')->count();
        $consoEpuises   = Consommable::where('statut', 'Épuisé')->count();

        return [

            // 1. Total équipements
            Stat::make('Équipements totales', $totalActifs)
                ->description("{$reformes} réformé(s) non comptabilisé(s)")
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),

            //  2. Total consommables 
            Stat::make('Articles consommables', $consoTotal)
                ->description('Nombre total de références enregistrées')
                ->descriptionIcon('heroicon-m-beaker')
                ->color('primary'),

            //  3. Consommables sous seuil
            Stat::make('Sous seuil minimal', $consoSousSeuil)
                ->description('Articles à réapprovisionner')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($consoSousSeuil > 0 ? 'warning' : 'success'),

            // 4. Consommables épuisés
            Stat::make('Épuisés', $consoEpuises)
                ->description('Articles sans stock disponible')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($consoEpuises > 0 ? 'danger' : 'success'),
        ];
    }
    }
