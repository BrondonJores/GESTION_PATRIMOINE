<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Consommable;
use Filament\Widgets\ChartWidget;

class RepartitionCategorieChartWidget extends ChartWidget
{
    protected ?string $heading = 'Répartition par catégorie';
    protected static ?int $sort = 40;

    public ?string $filter = 'article';

    protected function getFilters(): ?array
    {
        return [
            'article'     => 'Équipements',
            'consommable' => 'Consommables',
        ];
    }

    protected function getData(): array
    {
        $colors = [
            '#1B4332', '#2D6A4F', '#40916C',
            '#52B788', '#74C69D', '#95D5B2', '#B7E4C7',
        ];

        if ($this->filter === 'article') {
            $data = Article::query()
                ->whereNotIn('statut', [Article::REFORME])
                ->join('categories', 'articles.categorie_id', '=', 'categories.id')
                ->join('familles', 'categories.famille_id', '=', 'familles.id')
                ->selectRaw('familles.nom_famille as label, COUNT(articles.id) as total')
                ->groupBy('familles.id', 'familles.nom_famille')
                ->orderByDesc('total')
                ->get();
        } else {
            $data = Consommable::query()
                ->join('categories', 'consommables.categorie_id', '=', 'categories.id')
                ->join('familles', 'categories.famille_id', '=', 'familles.id')
                ->selectRaw('familles.nom_famille as label, COUNT(consommables.id) as total')
                ->groupBy('familles.id', 'familles.nom_famille')
                ->orderByDesc('total')
                ->get();
        }

        if ($data->isEmpty()) {
            return [
                'datasets' => [['data' => [], 'backgroundColor' => []]],
                'labels'   => [],
            ];
        }

        $total = $data->sum('total');
        $labels = $data->map(fn($r) =>
            $r->label . ' ' . ($total > 0 ? round(($r->total / $total) * 100) : 0) . '%'
        )->toArray();

        return [
            'datasets' => [[
                'data'            => $data->pluck('total')->toArray(),
                'backgroundColor' => array_slice($colors, 0, $data->count()),
                'borderColor'     => '#FFFFFF',
                'borderWidth'     => 2,
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