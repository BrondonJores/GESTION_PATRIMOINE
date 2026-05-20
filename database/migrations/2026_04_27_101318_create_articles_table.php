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
    Schema::create('articles', function (Blueprint $table) {
        $table->id();
        $table->string('numero_reference', 100)->unique();
        $table->string('code_ancien', 100)->nullable();
        $table->string('designation', 255);
         $table->enum('statut', [
            'Disponible',
            'Affecté',
            'En_maintenance',
            'Réformé',
        ])->default('Disponible');
        $table->text('observations')->nullable();
        $table->foreignId('categorie_id')->constrained('categories')->onDelete('cascade');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
