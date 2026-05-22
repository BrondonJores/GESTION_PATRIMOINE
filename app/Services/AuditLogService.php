<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class AuditLogService
{
    private const ACTIONS_AUTORISEES = [
        'Création',
        'Modification',
        'Suppression',
        'Connexion',
        'Déconnexion',
        'Export',
        'Alerte',
        'Affectation',
        'Réaffectation',
        'Récupération',
    ];

    public function enregistrer(string $module, string $action, ?User $user = null, ?string $adresseIp = null): AuditLog
    {
        $this->validerModule($module);
        $this->validerAction($action);

        return AuditLog::create([
            'module' => $module,
            'action' => $action,
            'adresse_ip' => $adresseIp,
            'user_id' => $user?->id,
            'date_action' => Carbon::now(),
        ]);
    }

    public function creation(string $module, ?User $user = null, ?string $adresseIp = null): AuditLog
    {
        return $this->enregistrer($module, 'Création', $user, $adresseIp);
    }

    public function modification(string $module, ?User $user = null, ?string $adresseIp = null): AuditLog
    {
        return $this->enregistrer($module, 'Modification', $user, $adresseIp);
    }

    public function suppression(string $module, ?User $user = null, ?string $adresseIp = null): AuditLog
    {
        return $this->enregistrer($module, 'Suppression', $user, $adresseIp);
    }

    public function export(string $module, ?User $user = null, ?string $adresseIp = null): AuditLog
    {
        return $this->enregistrer($module, 'Export', $user, $adresseIp);
    }

    private function validerModule(string $module): void
    {
        if (trim($module) === '') {
            throw new InvalidArgumentException('Le module du journal est obligatoire.');
        }
    }

    private function validerAction(string $action): void
    {
        if (! in_array($action, self::ACTIONS_AUTORISEES, true)) {
            throw new InvalidArgumentException("L'action de journal [{$action}] n'est pas autorisée.");
        }
    }
}
