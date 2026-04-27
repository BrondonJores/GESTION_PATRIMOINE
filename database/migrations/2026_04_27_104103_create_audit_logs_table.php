<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->string('module', 100);
        $table->enum('action', ['Création', 'Modification', 'Suppression', 'Connexion', 'Déconnexion', 'Export', 'Alerte', 'Affectation', 'Réaffectation', 'Récupération']);
        $table->string('adresse_ip', 50)->nullable();
        $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
        $table->datetime('date_action');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
