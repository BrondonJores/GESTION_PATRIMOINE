<?php
$telephone = '+212635264352';
echo "Tentative d'envoi force a : " . $telephone . "\n";
$service = app(App\Services\NotificationService::class);
$user = new App\Models\User();
$user->telephone = $telephone;
$service->notifyUser($user, "Test SMS FORCE direct au numero verifie.", "SMS");
echo "Termine. Verifie les logs.\n";
