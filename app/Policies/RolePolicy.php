<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('assign roles');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('assign roles');
    }

    public function create(User $user): bool
    {
        return $user->can('assign roles');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('assign roles');
    }

    public function delete(User $user, Role $role): bool
    {
        return false;
    }

    public function restore(User $user, Role $role): bool
    {
        return false;
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }
}
