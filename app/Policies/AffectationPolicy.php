<?php

namespace App\Policies;

use App\Models\Affectation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AffectationPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) return true;
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view affectations');
    }

    public function view(User $user, Affectation $affectation): bool
    {
        return $user->can('view affectations');
    }

    public function create(User $user): bool
    {
        return $user->can('create affectations');
    }

    public function update(User $user, Affectation $affectation): bool
    {
        if (!is_null($affectation->date_recuperation)) return false;
        return $user->can('update affectations');
    }

    public function delete(User $user, Affectation $affectation): bool
    {
        if (is_null($affectation->date_recuperation)) return false;
        return $user->can('delete affectations');
    }
}