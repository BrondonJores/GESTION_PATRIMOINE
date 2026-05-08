<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use Filament\Widgets\ChartWidget;

class ArticlesStatutChartWidget extends ChartWidget
{
    protected  ?string $heading = 'Répartition des articles par statut';
    protected static ?int    $sort    = 20;

    protected function getData(): array
    {
        $disponibles = Article::where('statut', 'Disponible')->count();
        $affectes    = Article::where('statut', 'Affecté')->count();
        $maintenance = Article::where('statut', 'En_maintenance')->count();
        $reformes    = Article::where('statut', 'Réformé')->count();

        return [
            'datasets' => [[
                'data'            => [$disponibles, $affectes, $maintenance, $reformes],
                'backgroundColor' => [
                    'rgba(34, 197, 94, 0.8)',   // vert — Disponible
                    'rgba(234, 179, 8, 0.8)',    // jaune — Affecté
                    'rgba(107, 114, 128, 0.8)',  // gris  — Maintenance
                    'rgba(239, 68, 68, 0.8)',    // rouge — Réformé
                ],
                'borderColor' => ['#16a34a', '#ca8a04', '#4b5563', '#dc2626'],
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