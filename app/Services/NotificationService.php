<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function notifyUser(User $user, string $contenu, string $canal = 'InApp'): Notification
    {
        return Notification::create([
            'canal' => $canal,
            'contenu' => $contenu,
            'lu' => false,
            'date_envoi' => Carbon::now(),
            'user_id' => $user->id,
        ]);
    }

    /**
     * @param Collection<int, User> $users
     */
    public function notifyUsers(Collection $users, string $contenu, string $canal = 'InApp'): void
    {
        DB::transaction(function () use ($users, $contenu, $canal): void {
            $users->each(fn (User $user) => $this->notifyUser($user, $contenu, $canal));
        });
    }

    public function markAsRead(Notification $notification): Notification
    {
        $notification->forceFill(['lu' => true])->save();

        return $notification;
    }

    public function supportRecipients(): Collection
    {
        return User::permission('view notifications')->get();
    }
}
