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
        Schema::create('consommables', function (Blueprint $table) {
            $table->id();
              $table->string('designation', 150);

        // Rattaché au catalogue de catégories existant
        $table->foreignId('categorie_id')
              ->constrained('categories')
              ->onDelete('restrict');

        // Quantité actuellement disponible
        $table->integer('quantite_stock')->default(0);

        // Seuil déclenchant une alerte
        $table->integer('quantite_min')->nullable();

        // Statut calculé automatiquement par ConsommableObserver
        $table->enum('statut', [
            'Disponible',
            'Sous seuil',
            'Épuisé',
        ])->default('Disponible');
          $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consommables');
    }
};