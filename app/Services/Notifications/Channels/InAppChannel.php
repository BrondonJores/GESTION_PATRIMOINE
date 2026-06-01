<?php

namespace App\Services\Notifications\Channels;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Carbon;

class InAppChannel implements NotificationChannelInterface
{
    public function send(User $user, string $contenu, array $options = []): void
    {
        Notification::create([
            'canal' => 'InApp',
            'contenu' => $contenu,
            'lu' => false,
            'date_envoi' => Carbon::now(),
            'user_id' => $user->id,
        ]);
    }
}
