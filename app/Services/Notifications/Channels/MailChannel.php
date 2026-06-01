<?php

namespace App\Services\Notifications\Channels;

use App\Models\User;
use App\Mail\GenericNotificationMail;
use App\Services\ContactSettingService;
use Illuminate\Support\Facades\Mail;

class MailChannel implements NotificationChannelInterface
{
    public function send(User $user, string $contenu, array $options = []): void
    {
        if (!$user->email) return;

        $settings = app(ContactSettingService::class)->getSettings();
        
        $mail = new GenericNotificationMail(
            $contenu,
            $options['sujet'] ?? 'Notification Patrimoine'
        );

        // On définit l'émetteur dynamiquement si configuré
        if (!empty($settings['email_emetteur'])) {
            $mail->from($settings['email_emetteur'], $settings['nom_emetteur'] ?? null);
        }

        Mail::to($user->email)->send($mail);
    }
}
