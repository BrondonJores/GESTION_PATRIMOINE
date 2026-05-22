<?php

namespace App\Filament\Widgets;

use App\Models\Affectation;
use App\Models\Alerte;
use App\Models\AuditLog;
use App\Models\Notification;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class ActiviteRecentChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Activité récente';

    protected ?string $description = 'Alertes, notifications, affectations et logs sur les 14 derniers jours.';

    protected string $color = 'primary';

    protected ?string $maxHeight = '320px';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $start = CarbonImmutable::today()->subDays(13);
        $labels = collect(range(0, 13))
            ->map(fn (int $dayOffset): string => $start->addDays($dayOffset)->format('d/m'))
            ->all();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Alertes',
                    'data' => $this->dailyCounts(Alerte::query(), 'date_alerte', $start),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => '#ef4444',
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Notifications',
                    'data' => $this->dailyCounts(Notification::query(), 'date_envoi', $start),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => '#f59e0b',
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Affectations',
                    'data' => $this->dailyCounts(Affectation::query(), 'created_at', $start),
                    'borderColor' => '#14b8a6',
                    'backgroundColor' => '#14b8a6',
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Logs',
                    'data' => $this->dailyCounts(AuditLog::query(), 'date_action', $start),
                    'borderColor' => '#64748b',
                    'backgroundColor' => '#64748b',
                    'tension' => 0.35,
                ],
            ],
        ];
    }

    /**
     * @return array<int, int>
     */
    private function dailyCounts(Builder $query, string $dateColumn, CarbonImmutable $start): array
    {
        $counts = (clone $query)
            ->whereDate($dateColumn, '>=', $start)
            ->selectRaw("DATE({$dateColumn}) as day, COUNT(*) as aggregate")
            ->groupBy('day')
            ->pluck('aggregate', 'day');

        return collect(range(0, 13))
            ->map(fn (int $dayOffset): int => (int) ($counts[$start->addDays($dayOffset)->toDateString()] ?? 0))
            ->all();
    }
}
