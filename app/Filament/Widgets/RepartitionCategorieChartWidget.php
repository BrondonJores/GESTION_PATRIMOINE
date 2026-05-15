<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use Filament\Widgets\ChartWidget;

class RepartitionCategorieChartWidget extends ChartWidget
{
    protected  ?string $heading = 'Répartition par catégorie';
    protected static ?int    $sort    = 40 ;

     protected function getData(): array
    {
        $data = Article::query()
            ->where('is_archived', false)
            ->join('categories', 'articles.categorie_id', '=', 'categories.id')
            ->join('familles', 'categories.famille_id', '=', 'familles.id')
            ->selectRaw('familles.nom_famille as label, COUNT(articles.id) as total')
            ->groupBy('familles.id', 'familles.nom_famille')
            ->orderByDesc('total')
            ->get();

        $total = $data->sum('total');

        $labels = $data->map(fn ($row) =>
            $row->label . ' ' . ($total > 0 ? round(($row->total/$total)*100) : 0) . '%'
        )->toArray();

        return [
            'datasets' => [[
                'data'            => $data->pluck('total')->toArray(),
                'backgroundColor' => [
                    '#1B4332','#2D6A4F','#40916C',
                    '#52B788','#74C69D','#95D5B2','#B7E4C7',
                ],
                'borderColor' => '#FFFFFF',
                'borderWidth' => 2,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display'  => true,
                    'position' => 'right',
                ],
            ],
        ];
    }
}