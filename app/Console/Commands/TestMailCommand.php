<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class TestMailCommand extends Command
{
    protected $signature = 'test:mail {email}';
    protected $description = 'Envoie un mail de test via le NotificationService';

    public function handle(NotificationService $notificationService)
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Utilisateur non trouvé avec l'email : {$email}");
            return 1;
        }

        $this->info("Tentative d'envoi d'un mail de test à {$email}...");

        try {
            $notificationService->notifyUser(
                $user,
                "Ceci est un test du système de notification de Gestion de Patrimoine. Si vous recevez ce message, votre configuration Gmail est correcte ! 🚀",
                'Email',
                ['sujet' => 'Test de Notification Réel']
            );
            $this->info("Commande envoyée ! Vérifiez la boîte de réception (et les spams).");
        } catch (\Exception $e) {
            $this->error("Erreur lors de l'envoi : " . $e->getMessage());
        }

        return 0;
    }
}
