<?php

namespace App\Filament\Widgets;

use App\Models\Affectation;
use App\Models\Bloc;
use Filament\Widgets\ChartWidget;

class RepartitionBlocChartWidget extends ChartWidget
{
    protected ?string $heading = 'Répartition des équipements affectés par bloc';
    protected static ?int $sort = 30;

    protected function getData(): array
    {
        $data = Bloc::query()
            ->join('affectations', 'blocs.id', '=', 'affectations.bloc_id')
            ->where('affectations.type', 'article')         // uniquement les équipements
            ->whereNull('affectations.date_recuperation')   // uniquement les actives
            ->selectRaw('blocs.nom_bloc as label, COUNT(affectations.id) as total')
            ->groupBy('blocs.id', 'blocs.nom_bloc')
            ->orderByDesc('total')
            ->get();

        if ($data->isEmpty()) {
            return [
                'datasets' => [[
                    'data'            => [],
                    'backgroundColor' => '#2D6A4F',
                ]],
                'labels' => [],
            ];
        }

        return [
            'datasets' => [[
                'label'                => 'Équipements affectés',
                'data'                 => $data->pluck('total')->toArray(),
                'backgroundColor'      => '#2D6A4F',
                'borderColor'          => '#1B4332',
                'borderWidth'          => 1,
                'borderRadius'         => 4,
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
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => ['stepSize' => 1], // entiers uniquement
                    'grid'        => ['color' => 'rgba(0,0,0,0.05)'],
                ],
                'x' => [
                    'grid' => ['display' => false],
                ],
            ],
        ];
    }
}