<?php

namespace App\Observers;

use App\Models\Alerte;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Support\Alertes\StockAlertType;
use Illuminate\Support\Carbon;

class AlerteObserver
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly AuditLogService $auditLogs,
    ) {
    }

    public function created(Alerte $alerte): void
    {
        $this->auditLogs->enregistrer("Alertes - #{$alerte->id}", 'Alerte');

        $this->notifications->notifyUsers(
            $this->notifications->supportRecipients(),
            StockAlertType::label($alerte->type_alerte) . " pour l'article #{$alerte->article_id}.",
            $alerte->canal,
        );
    }

    public function updating(Alerte $alerte): void
    {
        if ($alerte->isDirty('statut') && $alerte->statut === 'Résolu' && $alerte->date_traitement === null) {
            $alerte->date_traitement = Carbon::now();
        }
    }

    public function updated(Alerte $alerte): void
    {
        if (! $alerte->wasChanged('statut')) {
            return;
        }

        $this->auditLogs->modification("Alertes - #{$alerte->id}");

        $this->notifications->notifyUsers(
            $this->notifications->supportRecipients(),
            'Alerte #' . $alerte->id . ' mise à jour : ' . StockAlertType::label($alerte->type_alerte) . " - {$alerte->statut}.",
            'InApp',
        );
    }
}
