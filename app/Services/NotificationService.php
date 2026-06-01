<?php

namespace App\Services;

use App\Models\User;
use App\Services\Notifications\Channels\InAppChannel;
use App\Services\Notifications\Channels\MailChannel;
use App\Services\Notifications\Channels\SmsChannel;
use App\Services\Notifications\Channels\NotificationChannelInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /** @var array<string, NotificationChannelInterface> */
    protected array $channels = [];

    public function __construct()
    {
        // Enregistrement des canaux disponibles
        $this->channels = [
            'InApp' => new InAppChannel(),
            'Email' => new MailChannel(),
            'SMS'   => new SmsChannel(),
        ];
    }

    /**
     * Notifie un utilisateur via un ou plusieurs canaux.
     * Canal peut être 'InApp', 'Email', 'SMS', 'Tous' ou un array ['Email', 'InApp']
     */
    public function notifyUser(User $user, string $contenu, string|array $canal = 'InApp', array $options = []): void
    {
        $canauxAUtiliser = $this->resoudreCanaux($canal);

        foreach ($canauxAUtiliser as $nomCanal) {
            if (isset($this->channels[$nomCanal])) {
                try {
                    $this->channels[$nomCanal]->send($user, $contenu, $options);
                } catch (\Throwable $e) {
                    // On logue l'erreur mais on continue pour les autres canaux
                    \Illuminate\Support\Facades\Log::error("Erreur d'envoi notification via {$nomCanal} : " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Notifie plusieurs utilisateurs.
     */
    public function notifyUsers(Collection $users, string $contenu, string|array $canal = 'InApp', array $options = []): void
    {
        DB::transaction(function () use ($users, $contenu, $canal, $options): void {
            $users->each(fn (User $user) => $this->notifyUser($user, $contenu, $canal, $options));
        });
    }

    public function supportRecipients(): Collection
    {
        return User::permission('view notifications')->get();
    }

    protected function resoudreCanaux(string|array $canal): array
    {
        if (is_array($canal)) return $canal;

        if ($canal === 'Tous') {
            return array_keys($this->channels);
        }

        return [$canal];
    }
}
