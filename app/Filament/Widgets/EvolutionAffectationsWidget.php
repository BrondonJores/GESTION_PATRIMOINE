<?php

namespace App\Filament\Widgets;

use App\Models\Affectation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class EvolutionAffectationsWidget extends ChartWidget
{
    protected ?string $heading = 'Évolution Mensuelle des Affectations';
    protected static ?int $sort = 50;

    protected function getData(): array
    {
        $mois = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $mois[] = $date->translatedFormat('M');
            $data[] = Affectation::whereYear('date_affectation', $date->year)
                ->whereMonth('date_affectation', $date->month)
                ->sum('quantite');
        }

        return [
            'datasets' => [[
                'label'           => 'affectations',
                'data'            => $data,
                'borderColor'     => '#2D6A4F',
                'backgroundColor' => 'rgba(45, 106, 79, 0.1)',
                'pointBackgroundColor' => '#1B4332',
                'pointRadius'     => 5,
                'tension'         => 0.4,
                'fill'            => false,
            ]],
            'labels' => $mois,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'bottom'],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(0,0,0,0.05)']],
                'x' => ['grid' => ['color' => 'rgba(0,0,0,0.05)']],
            ],
        ];
    }
}