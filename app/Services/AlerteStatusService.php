<?php

namespace App\Services;

use App\Models\Alerte;

class AlerteStatusService
{
    public function prendreEnCharge(Alerte $alerte): Alerte
    {
        $alerte->forceFill(['statut' => 'En_cours'])->save();

        return $alerte;
    }

    public function marquerResolue(Alerte $alerte, ?string $noteResolution = null): Alerte
    {
        $alerte->forceFill([
            'statut' => 'Résolu',
            'note_resolution' => $noteResolution,
        ])->save();

        return $alerte;
    }
}
