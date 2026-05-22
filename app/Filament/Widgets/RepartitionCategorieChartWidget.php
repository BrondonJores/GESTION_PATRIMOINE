<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Categorie;
use App\Models\Consommable;
use Filament\Widgets\ChartWidget;

class RepartitionCategorieChartWidget extends ChartWidget
{
    protected ?string $heading = 'Répartition par catégorie';
    protected static ?int $sort = 40;

    // Filtre actif par défaut : équipements
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
        if ($this->filter === 'article') {
            // ── Équipements ────────────────────────────────────────
            $data = Article::query()
                ->where('statut', '!=', Article::REFORME)
                ->join('categories', 'articles.categorie_id', '=', 'categories.id')
                ->selectRaw('categories.nom_categorie as label, COUNT(articles.id) as total')
                ->groupBy('categories.id', 'categories.nom_categorie')
                ->orderByDesc('total')
                ->get();

        } else {
            // ── Consommables ───────────────────────────────────────
            $data = Consommable::query()
                ->join('categories', 'consommables.categorie_id', '=', 'categories.id')
                ->selectRaw('categories.nom_categorie as label, COUNT(consommables.id) as total')
                ->groupBy('categories.id', 'categories.nom_categorie')
                ->orderByDesc('total')
                ->get();
        }

        if ($data->isEmpty()) {
            return [
                'datasets' => [['data' => [], 'backgroundColor' => []]],
                'labels'   => [],
            ];
        }

        $palette = [
            '#1B4332', '#2D6A4F', '#40916C',
            '#52B788', '#74C69D', '#95D5B2',
            '#B7E4C7', '#D8F3DC', '#081C15',
            '#1565C0', '#1976D2', '#42A5F5',
            '#EF6C00', '#F57C00', '#FFA726',
            '#6A1B9A', '#7B1FA2', '#AB47BC',
            '#B71C1C', '#C62828', '#EF5350',
        ];

        $backgroundColors = $data->keys()
            ->map(fn ($i) => $palette[$i % count($palette)])
            ->toArray();

        $total = $data->sum('total');

        $labels = $data->map(fn ($r) =>
    $r->label . ' (' .
    ($total > 0 ? round(($r->total / $total) * 100) : 0) .
    '% - ' . $r->total . ')'
)->toArray();
        return [
            'datasets' => [[
                'data'            => $data->pluck('total')->toArray(),
                'backgroundColor' => $backgroundColors,
                'borderColor'     => '#FFFFFF',
                'borderWidth'     => 2,
                'hoverOffset'     => 6,
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
                    'labels'   => [
                        'font'    => ['size' => 11],
                        'padding' => 12,
                        // Boîte de couleur carrée — plus lisible
                        'usePointStyle' => false,
                        'boxWidth'      => 14,
                    ],
                ],
                'tooltip' => [
    'enabled' => true,

                ],
            ],
            'maintainAspectRatio' => true,
        ];
    }
}