<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class AffectationsChartWidget extends ChartWidget
{
    protected ?string $heading = 'Affectations Chart Widget';
        protected static ?int    $sort    = 30;


    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
