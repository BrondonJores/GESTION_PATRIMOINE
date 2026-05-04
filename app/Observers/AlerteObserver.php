<?php

namespace App\Observers;

use App\Models\Alerte;
use App\Services\NotificationService;
use Illuminate\Support\Carbon;

class AlerteObserver
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function created(Alerte $alerte): void
    {
        $this->notifications->notifyUsers(
            $this->notifications->supportRecipients(),
            "Nouvelle alerte {$alerte->statut} pour l'article #{$alerte->article_id}.",
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

        $this->notifications->notifyUsers(
            $this->notifications->supportRecipients(),
            "Alerte #{$alerte->id} mise à jour : {$alerte->statut}.",
            'InApp',
        );
    }
}
