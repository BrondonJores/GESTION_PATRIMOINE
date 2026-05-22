<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Article;

class ArticlesStatutChartWidget extends ChartWidget
{
    protected  ?string $heading = 'Répartition des articles par statut';
    protected static ?int    $sort    = 20;

protected function getData(): array
    {
        $disponibles = Article::where('statut', Article::DISPONIBLE)->count();
        $affectes    = Article::where('statut', Article::AFFECTE)->count();
        $maintenance = Article::where('statut', Article::MAINTENANCE)->count();
        $reformes    = Article::where('statut', Article::REFORME)->count();

        return [
            'datasets' => [[
                'data'            => [$disponibles, $affectes, $maintenance, $reformes],
                'backgroundColor' => [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(234, 179, 8, 0.8)',
                    'rgba(107, 114, 128, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
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