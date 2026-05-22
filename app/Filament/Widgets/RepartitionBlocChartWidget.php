<?php

namespace App\Filament\Widgets;

use App\Models\Affectation;
use Filament\Widgets\ChartWidget;

class RepartitionBlocChartWidget extends ChartWidget
{
    protected ?string $heading = 'Répartition par Bloc';
    protected static ?int $sort = 30;

    public ?string $filter = 'tous';

    protected function getFilters(): ?array
    {
        return [
            'tous'        => 'Tous',
            'article'     => 'Équipements',
            'consommable' => 'Consommables',
        ];
    }

    protected function getData(): array
    {
        $query = Affectation::query()
            ->leftJoin('salles', 'salles.id', '=', 'affectations.salle_id')
            ->join('blocs', function ($join): void {
                $join->on('blocs.id', '=', 'affectations.bloc_id')
                    ->orOn('blocs.id', '=', 'salles.bloc_id');
            })
            ->whereNull('affectations.date_recuperation');

        // Filtrer par type
        if ($this->filter !== 'tous') {
            $query->where('affectations.type', $this->filter);
        }

        $data = $query
            ->selectRaw('blocs.nom_bloc as label, SUM(affectations.quantite) as total')
            ->groupBy('blocs.id', 'blocs.nom_bloc')
            ->orderByDesc('total')
            ->get();

        if ($data->isEmpty()) {
            return [
                'datasets' => [['data' => [], 'backgroundColor' => []]],
                'labels'   => [],
            ];
        }

        $colors = [
            '#2D6A4F', '#40916C', '#52B788',
            '#74C69D', '#95D5B2', '#B7E4C7',
        ];

        $backgroundColors = $data->keys()->map(fn($i) =>
            $colors[$i % count($colors)]
        )->toArray();

        return [
            'datasets' => [[
                'label'           => $this->filter === 'consommable'
                    ? 'Consommables affectés'
                    : ($this->filter === 'article'
                        ? 'Équipements affectés'
                        : 'Total affecté'),
                'data'            => $data->pluck('total')->toArray(),
                'backgroundColor' => $backgroundColors,
                'borderColor'     => '#1B4332',
                'borderWidth'     => 1,
                'borderRadius'    => 4,
                'hoverBackgroundColor' => '#40916C',
            ]],
            'labels' => $data->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales'  => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => ['color' => 'rgba(0,0,0,0.05)'],
                ],
                'x' => ['grid' => ['display' => false]],
            ],
        ];
    }
}
