<?php

namespace App\Policies;

use App\Models\Rapport;
use App\Models\User;

class RapportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view rapports');
    }

    public function view(User $user, Rapport $rapport): bool
    {
        return $user->can('view rapports');
    }

    public function create(User $user): bool
    {
        return $user->can('create rapports');
    }

    public function update(User $user, Rapport $rapport): bool
    {
        return $user->can('create rapports');
    }

    public function delete(User $user, Rapport $rapport): bool
    {
        return $user->can('delete rapports');
    }

    public function restore(User $user, Rapport $rapport): bool
    {
        return false;
    }

    public function forceDelete(User $user, Rapport $rapport): bool
    {
        return false;
    }

    public function export(User $user): bool
    {
        return $user->can('export rapports');
    }
}
