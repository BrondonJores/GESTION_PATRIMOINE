<?php

namespace App\Services;

use App\Models\Alerte;
use App\Models\User;
use Illuminate\Support\Collection;

class AlerteCounterService
{
    public function openFor(User $user): int
    {
        if (! $user->can('view alertes')) {
            return 0;
        }

        return Alerte::query()
            ->where('statut', '!=', 'Résolu')
            ->count();
    }

    /**
     * @return Collection<int, Alerte>
     */
    public function latestOpenFor(User $user, int $limit = 5): Collection
    {
        if (! $user->can('view alertes')) {
            return collect();
        }

        return Alerte::query()
            ->with('consommable')
            ->where('statut', '!=', 'Résolu')
            ->latest('date_alerte')
            ->limit($limit)
            ->get();
    }
}
