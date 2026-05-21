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
   if (Schema::hasTable('alertes')) return;
Schema::create('alertes', function (Blueprint $table) {
        $table->id();
        $table->enum('statut', ['Non_traité', 'En_cours', 'Résolu'])->default('Non_traité');
        $table->enum('canal', ['Email', 'SMS', 'InApp', 'Tous'])->default('Tous');
        $table->text('retour')->nullable();
        $table->datetime('date_alerte');
        $table->datetime('date_traitement')->nullable();
        $table->foreignId('consommable_id')->constrained('consommables')->onDelete('cascade');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertes');
    }
};