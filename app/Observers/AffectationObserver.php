<?php

namespace App\Observers;

use App\Models\Affectation;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AffectationObserver
{
    public function created(Affectation $affectation): void
    {
        AuditLog::create([
            'module'      => 'Affectations',
            'action'      => 'Affectation',
            'adresse_ip'  => Request::ip(),
            'user_id'     => Auth::id(),
            'date_action' => now(),
        ]);
    }

    public function updated(Affectation $affectation): void
    {
        $dirty  = $affectation->getDirty();
        $action = array_key_exists('date_recuperation', $dirty) ? 'Récupération' : 'Modification';

        AuditLog::create([
            'module'      => 'Affectations',
            'action'      => $action,
            'adresse_ip'  => Request::ip(),
            'user_id'     => Auth::id(),
            'date_action' => now(),
        ]);
    }

    public function deleting(Affectation $affectation): void
    {
        AuditLog::create([
            'module'      => 'Affectations',
            'action'      => 'Suppression',
            'adresse_ip'  => Request::ip(),
            'user_id'     => Auth::id(),
            'date_action' => now(),
        ]);
    }
}