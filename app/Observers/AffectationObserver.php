<?php

namespace App\Observers;

use App\Models\Affectation;
use App\Models\AuditLog as LogModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AffectationObserver
{
    public function created(Affectation $affectation): void
    {
        LogModel::create([
            'module'      => 'Affectations',
            'action'      => 'Affectation',
            'adresse_ip'  => Request::ip(),
            'user_id'     => Auth::id(),
            'date_action' => now(),
        ]);
    }

    public function updated(Affectation $affectation): void
    {
        $action = isset($affectation->getDirty()['date_recuperation'])
            ? 'Récupération'
            : 'Modification';

        LogModel::create([
            'module'      => 'Affectations',
            'action'      => $action,
            'adresse_ip'  => Request::ip(),
            'user_id'     => Auth::id(),
            'date_action' => now(),
        ]);
    }
}