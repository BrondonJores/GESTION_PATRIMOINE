<?php

namespace App\Policies;

use App\Models\Alerte;
use App\Models\User;

class AlertePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view alertes');
    }

    public function view(User $user, Alerte $alerte): bool
    {
        return $user->can('view alertes');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Alerte $alerte): bool
    {
        return $user->can('traiter alertes');
    }

    public function delete(User $user, Alerte $alerte): bool
    {
        return false;
    }

    public function restore(User $user, Alerte $alerte): bool
    {
        return false;
    }

    public function forceDelete(User $user, Alerte $alerte): bool
    {
        return false;
    }
}
