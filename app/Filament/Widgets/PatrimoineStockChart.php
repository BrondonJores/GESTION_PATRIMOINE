<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use Filament\Widgets\ChartWidget;

class PatrimoineStockChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Stock par statut';

    protected ?string $description = 'Comparaison entre quantité disponible et nombre de références.';

    protected string $color = 'success';

    protected ?string $maxHeight = '280px';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'xl' => 1,
    ];

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $statuses = [
            'Disponible',
            'Affecté',
            'En_maintenance',
            'Réformé',
        ];

        $labels = [
            'Disponible',
            'Affecté',
            'Maintenance',
            'Réformé',
        ];

        $rows = Article::query()
            ->selectRaw('statut, COUNT(*) as articles_count, COALESCE(SUM(quantite), 0) as total_quantity')
            ->groupBy('statut')
            ->get()
            ->keyBy('statut');

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Quantité',
                    'data' => collect($statuses)
                        ->map(fn (string $status): int => (int) ($rows[$status]->total_quantity ?? 0))
                        ->all(),
                    'backgroundColor' => '#2563eb',
                    'borderColor' => '#2563eb',
                ],
                [
                    'label' => 'Références',
                    'data' => collect($statuses)
                        ->map(fn (string $status): int => (int) ($rows[$status]->articles_count ?? 0))
                        ->all(),
                    'backgroundColor' => '#14b8a6',
                    'borderColor' => '#14b8a6',
                ],
            ],
        ];
    }
}
