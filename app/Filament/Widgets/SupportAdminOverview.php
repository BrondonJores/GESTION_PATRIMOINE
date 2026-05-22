<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Alertes\AlerteResource;
use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Filament\Resources\Notifications\NotificationResource;
use App\Filament\Resources\Rapports\RapportResource;
use App\Models\Affectation;
use App\Models\Alerte;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Rapport;
use Carbon\CarbonImmutable;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class SupportAdminOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Supervision';

    protected ?string $description = 'Pilotage rapide du patrimoine, du stock et des actions récentes.';

    protected int|array|null $columns = [
        'default' => 1,
        'md' => 2,
        'xl' => 4,
    ];

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $openAlertes = Alerte::query()->where('statut', '!=', 'Résolu')->count();
        $resolvedAlertesToday = Alerte::query()
            ->where('statut', 'Résolu')
            ->whereDate('date_traitement', today())
            ->count();
        $unreadNotifications = Notification::query()->where('lu', false)->count();
        $notificationsToday = Notification::query()->whereDate('date_envoi', today())->count();
        $totalStock = Article::query()->sum('quantite');
        $lowStockArticles = Article::query()
            ->whereNotNull('quantite_min')
            ->whereColumn('quantite', '<=', 'quantite_min')
            ->count();
        $activeAffectations = Affectation::query()->whereNull('date_recuperation')->count();
        $reportsThisMonth = Rapport::query()
            ->whereBetween('date_generation', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return [
            Stat::make('Alertes à traiter', $openAlertes)
                ->description($resolvedAlertesToday.' résolue(s) aujourd’hui')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->color($openAlertes > 0 ? 'danger' : 'success')
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->chart($this->dailyCounts(Alerte::query()->where('statut', '!=', 'Résolu'), 'date_alerte'))
                ->url(AlerteResource::getUrl('index')),
            Stat::make('Notifications non lues', $unreadNotifications)
                ->description($notificationsToday.' envoyée(s) aujourd’hui')
                ->descriptionIcon(Heroicon::OutlinedPaperAirplane)
                ->color($unreadNotifications > 0 ? 'warning' : 'success')
                ->icon(Heroicon::OutlinedBell)
                ->chart($this->dailyCounts(Notification::query(), 'date_envoi'))
                ->url(NotificationResource::getUrl('index')),
            Stat::make('Stock total', number_format((int) $totalStock, 0, ',', ' '))
                ->description($lowStockArticles.' article(s) sous seuil')
                ->descriptionIcon(Heroicon::OutlinedExclamationCircle)
                ->descriptionColor($lowStockArticles > 0 ? 'danger' : 'success')
                ->color($lowStockArticles > 0 ? 'warning' : 'success')
                ->icon(Heroicon::OutlinedCube),
            Stat::make('Affectations actives', $activeAffectations)
                ->description('Matériel actuellement affecté')
                ->color('info')
                ->icon(Heroicon::OutlinedArrowPathRoundedSquare)
                ->chart($this->dailyCounts(Affectation::query(), 'created_at')),
            Stat::make('Rapports ce mois', $reportsThisMonth)
                ->description(Rapport::query()->count().' rapport(s) au total')
                ->color('primary')
                ->icon(Heroicon::OutlinedDocumentChartBar)
                ->chart($this->dailyCounts(Rapport::query(), 'date_generation'))
                ->url(RapportResource::getUrl('index')),
            Stat::make('Événements journalisés', AuditLog::query()->count())
                ->description('Activité des 7 derniers jours')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color('gray')
                ->icon(Heroicon::OutlinedClipboardDocumentList)
                ->chart($this->dailyCounts(AuditLog::query(), 'created_at'))
                ->url(AuditLogResource::getUrl('index')),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function dailyCounts(Builder $query, string $dateColumn): array
    {
        $start = CarbonImmutable::today()->subDays(6);

        $counts = (clone $query)
            ->whereDate($dateColumn, '>=', $start)
            ->selectRaw("DATE({$dateColumn}) as day, COUNT(*) as aggregate")
            ->groupBy('day')
            ->pluck('aggregate', 'day');

        return collect(range(0, 6))
            ->map(fn (int $dayOffset): int => (int) ($counts[$start->addDays($dayOffset)->toDateString()] ?? 0))
            ->all();
    }
}
