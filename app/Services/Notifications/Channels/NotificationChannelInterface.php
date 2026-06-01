<?php

namespace App\Services\Notifications\Channels;

use App\Models\User;

interface NotificationChannelInterface
{
    /**
     * Envoie une notification à un utilisateur.
     *
     * @param User $user L'utilisateur destinataire.
     * @param string $contenu Le message à envoyer.
     * @param array $options Options supplémentaires (sujet, etc.).
     * @return void
     */
    public function send(User $user, string $contenu, array $options = []): void;
}
