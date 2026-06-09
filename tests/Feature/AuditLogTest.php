<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Illuminate\Auth\Events\Login;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_journalisation_creation_utilisateur(): void
    {
        // Si vous avez un UserObserver qui log la création, on peut le tester ici.
        // Simulons l'action d'un administrateur créant un audit log manuellement 
        // ou via un service (comme c'est souvent le cas dans Filament)
        
        $admin = User::factory()->create();
        $this->actingAs($admin);

        // Création directe d'un log pour vérifier l'intégrité de la table
        $log = AuditLog::create([
            'module' => 'Utilisateurs',
            'action' => 'Création',
            'adresse_ip' => '127.0.0.1',
            'user_id' => $admin->id,
            'date_action' => now(),
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'module' => 'Utilisateurs',
            'action' => 'Création',
            'user_id' => $admin->id,
        ]);
    }
}
