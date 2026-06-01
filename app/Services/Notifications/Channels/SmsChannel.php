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

        // Normalisation du numéro : enlève le 0 après l'indicatif (ex: +21206... -> +2126...)
        $telephone = $user->telephone;
        if (str_starts_with($telephone, '+')) {
            // Remplace +XXX0 par +XXX (seulement si le 0 est juste après l'indicatif)
            // On peut aussi faire plus simple : si ça commence par + et qu'il y a un 0 après 2 ou 3 chiffres
            $telephone = preg_replace('/^(\+\d{1,3})0(\d+)/', '$1$2', $telephone);
        }

        $settings = app(ContactSettingService::class)->getSettings();

        // Si Twilio est configuré, on envoie pour de vrai
        if (!empty($settings['twilio_sid']) && !empty($settings['twilio_token']) && !empty($settings['twilio_number'])) {
            try {
                Log::info("Tentative d'envoi SMS Twilio vers : {$telephone} (Original: {$user->telephone})");
                $client = new Client($settings['twilio_sid'], $settings['twilio_token']);
                $client->messages->create(
                    $telephone,
                    [
                        'from' => $settings['twilio_number'],
                        'body' => $contenu
                    ]
                );
                Log::info("SMS envoyé avec succès via Twilio à {$telephone}");
                return;
            } catch (\Exception $e) {
                Log::error("Erreur Twilio lors de l'envoi à {$telephone} : " . $e->getMessage());
                // On laisse couler vers le log en cas d'erreur
            }
        }

        // Fallback ou mode simulation
        Log::info("SIMULATION SMS à {$telephone} (Emetteur: {$settings['telephone_emetteur']}) : {$contenu}");
    }
}
