<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationCounterService
{
    public function unreadFor(User $user): int
    {
        if (! $user->can('view notifications')) {
            return 0;
        }

        return Notification::query()
            ->where('lu', false)
            ->when(
                ! $user->hasRole('admin'),
                fn ($query) => $query->where(fn ($query) => $query
                    ->whereNull('user_id')
                    ->orWhere('user_id', $user->id)),
            )
            ->count();
    }
}
