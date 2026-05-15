<?php

namespace App\Filament\Widgets;

use App\Models\Stock;
use App\Models\Bloc;
use Filament\Widgets\ChartWidget;

class RepartitionBlocChartWidget extends ChartWidget
{
    protected  ?string $heading = 'Répartition par Bloc';
    protected static ?int    $sort    = 30;

    protected function getData(): array
    {
        // Quantité affectée par bloc via la table stocks
        $data = Bloc::query()
            ->join('salles', 'blocs.id', '=', 'salles.bloc_id')
            ->join('affectations', 'salles.id', '=', 'affectations.salle_id')
            ->whereNull('affectations.date_recuperation')
            ->selectRaw('blocs.nom_bloc as label, SUM(affectations.quantite) as total')
            ->groupBy('blocs.id', 'blocs.nom_bloc')
            ->orderByDesc('total')
            ->get();

        if ($data->isEmpty()) {
            return [
                'datasets' => [['data' => [], 'backgroundColor' => '#2D6A4F']],
                'labels'   => [],
            ];
        }

        return [
            'datasets' => [[
                'label'           => 'Unités affectées',
                'data'            => $data->pluck('total')->toArray(),
                'backgroundColor' => '#2D6A4F',
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
                'y' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(0,0,0,0.05)']],
                'x' => ['grid' => ['display' => false]],
            ],
        ];
    }
}