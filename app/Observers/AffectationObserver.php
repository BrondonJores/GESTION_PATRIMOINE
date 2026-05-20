<?php
// app/Observers/AffectationObserver.php

namespace App\Observers;

use App\Models\Affectation;
use App\Models\AuditLog as LogModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AffectationObserver
{
    public function created(Affectation $affectation): void
    {
        $action = $affectation->estPourArticle()
            ? 'Affectation article'
            : 'Affectation consommable';

        LogModel::create([
            'module'      => 'Affectations',
            'action'      => $action,
            'adresse_ip'  => Request::ip(),
            'user_id'     => Auth::id(),
            'date_action' => now(),
        ]);
    }

    public function updated(Affectation $affectation): void
    {
        $action = $affectation->getDirty()['date_recuperation'] ?? null
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