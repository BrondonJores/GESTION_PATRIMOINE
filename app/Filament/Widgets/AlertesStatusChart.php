<?php

namespace App\Filament\Widgets;

use App\Models\Alerte;
use Filament\Widgets\ChartWidget;

class AlertesStatusChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Répartition des alertes';

    protected ?string $description = 'Volume par statut de traitement.';

    protected string $color = 'warning';

    protected ?string $maxHeight = '280px';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'xl' => 1,
    ];

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $statuses = [
            'Non_traité' => 'Non traitées',
            'En_cours' => 'En cours',
            'Résolu' => 'Résolues',
        ];

        $counts = Alerte::query()
            ->selectRaw('statut, COUNT(*) as aggregate')
            ->groupBy('statut')
            ->pluck('aggregate', 'statut');

        return [
            'labels' => array_values($statuses),
            'datasets' => [
                [
                    'data' => collect(array_keys($statuses))
                        ->map(fn (string $status): int => (int) ($counts[$status] ?? 0))
                        ->all(),
                    'backgroundColor' => [
                        '#ef4444',
                        '#f59e0b',
                        '#22c55e',
                    ],
                    'borderWidth' => 0,
                ],
            ],
        ];
    }
}
