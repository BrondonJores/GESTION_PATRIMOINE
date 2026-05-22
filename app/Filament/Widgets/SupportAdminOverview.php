<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Alertes\AlerteResource;
use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Filament\Resources\Notifications\NotificationResource;
use App\Filament\Resources\Rapports\RapportResource;
use App\Models\Alerte;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Rapport;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupportAdminOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Supervision';

    protected ?string $description = 'État rapide des alertes, notifications, rapports , journaux et articles.';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        return [
            Stat::make('Alertes à traiter', Alerte::query()->where('statut', '!=', 'Résolu')->count())
                ->description('Voir les alertes ouvertes')
                ->color('danger')
                ->icon(Heroicon::OutlinedExclamationTriangle)
                ->url(AlerteResource::getUrl('index')),
            Stat::make('Notifications non lues', Notification::query()->where('lu', false)->count())
                ->description('Consulter les notifications')
                ->color('warning')
                ->icon(Heroicon::OutlinedBell)
                ->url(NotificationResource::getUrl('index')),
            Stat::make('Rapports générés', Rapport::query()->count())
                ->description('Accéder aux exports')
                ->color('success')
                ->icon(Heroicon::OutlinedDocumentChartBar)
                ->url(RapportResource::getUrl('index')),
            Stat::make('Événements journalisés', AuditLog::query()->count())
                ->description('Auditer les actions')
                ->color('gray')
                ->icon(Heroicon::OutlinedClipboardDocumentList)
                ->url(AuditLogResource::getUrl('index')),
        ];
    }
}
