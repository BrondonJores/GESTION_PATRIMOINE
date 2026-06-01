<?php
$user = App\Models\User::whereNotNull('telephone')->first();
if ($user) {
    echo "Envoi d'un test à : " . $user->telephone . "\n";
    app(App\Services\NotificationService::class)->notifyUser($user, "Test de notification SMS après correction du format.", "SMS");
} else {
    echo "Aucun utilisateur avec un numéro de téléphone trouvé.\n";
}
