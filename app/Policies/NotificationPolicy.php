<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view notifications');
    }

    public function view(User $user, Notification $notification): bool
    {
        return $user->can('view notifications')
            && ($notification->user_id === null || $notification->user_id === $user->id || $user->hasRole('admin'));
    }

    public function create(User $user): bool
    {
        return $user->can('view notifications');
    }

    public function update(User $user, Notification $notification): bool
    {
        return $user->can('view notifications');
    }

    public function delete(User $user, Notification $notification): bool
    {
        return $user->can('delete notifications');
    }

    public function restore(User $user, Notification $notification): bool
    {
        return false;
    }

    public function forceDelete(User $user, Notification $notification): bool
    {
        return false;
    }
}
