<?php

namespace App\Filament\Widgets;

use App\Models\Stock;
use Filament\Widgets\ChartWidget;

class ArticlesStatutChartWidget extends ChartWidget
{
    protected  ?string $heading = 'Répartition des articles par statut';
    protected static ?int    $sort    = 20;

    protected function getData(): array
    {
        // Somme des quantités par statut dans la table stocks
        $disponible  = Stock::where('statut', 'Disponible')->sum('quantite');
        $affecte     = Stock::where('statut', 'Affecté')->sum('quantite');
        $maintenance = Stock::where('statut', 'En_maintenance')->sum('quantite');
        $reforme     = Stock::where('statut', 'Réformé')->sum('quantite');

        return [
            'datasets' => [[
                'data'            => [$disponible, $affecte, $maintenance, $reforme],
                'backgroundColor' => [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(234, 179, 8, 0.8)',
                    'rgba(107, 114, 128, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                ],
                'borderColor' => ['#16a34a','#ca8a04','#4b5563','#dc2626'],
                'borderWidth' => 2,
            ]],
            'labels' => ['Disponible', 'Affecté', 'En maintenance', 'Réformé'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}