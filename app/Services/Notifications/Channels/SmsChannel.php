<?php

namespace App\Services\Notifications\Channels;

use App\Models\User;
use App\Services\ContactSettingService;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SmsChannel implements NotificationChannelInterface
{
    public function send(User $user, string $contenu, array $options = []): void
    {
        if (!$user->telephone) return;

        $settings = app(ContactSettingService::class)->getSettings();

        // Si Twilio est configuré, on envoie pour de vrai
        if (!empty($settings['twilio_sid']) && !empty($settings['twilio_token']) && !empty($settings['twilio_number'])) {
            try {
                $client = new Client($settings['twilio_sid'], $settings['twilio_token']);
                $client->messages->create(
                    $user->telephone,
                    [
                        'from' => $settings['twilio_number'],
                        'body' => $contenu
                    ]
                );
                return;
            } catch (\Exception $e) {
                Log::error("Erreur Twilio : " . $e->getMessage());
                // On laisse couler vers le log en cas d'erreur
            }
        }

        // Fallback ou mode simulation
        Log::info("SIMULATION SMS à {$user->telephone} (Emetteur: {$settings['telephone_emetteur']}) : {$contenu}");
    }
}
