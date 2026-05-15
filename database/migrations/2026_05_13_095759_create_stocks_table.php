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
    Schema::create('stocks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('article_id')
              ->constrained('articles')
              ->onDelete('cascade');
        $table->enum('statut', [
            'Disponible',
            'Affecté',
            'En_maintenance',
            'Réformé',
        ]);
        $table->integer('quantite')->default(0);
        $table->timestamps();

        // Un seul enregistrement par article/statut
        $table->unique(['article_id', 'statut']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
