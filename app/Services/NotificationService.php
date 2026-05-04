<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    private const CANAUX_AUTORISES = ['Email', 'SMS', 'InApp', 'Tous'];

    public function notifyUser(User $user, string $contenu, string $canal = 'InApp'): Notification
    {
        $this->validerCanal($canal);

        return Notification::create([
            'canal' => $canal,
            'contenu' => $contenu,
            'lu' => false,
            'date_envoi' => Carbon::now(),
            'user_id' => $user->id,
        ]);
    }

    /**
     * Notifie plusieurs utilisateurs dans une même transaction.
     *
     * @param Collection<int, User> $users
     */
    public function notifyUsers(Collection $users, string $contenu, string $canal = 'InApp'): void
    {
        $this->validerCanal($canal);

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

    private function validerCanal(string $canal): void
    {
        if (! in_array($canal, self::CANAUX_AUTORISES, true)) {
            throw new \InvalidArgumentException("Le canal de notification [{$canal}] n'est pas autorisé.");
        }
    }
}
