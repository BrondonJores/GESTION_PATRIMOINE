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
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            // Discriminant : article non consommable ou consommable
            $table->enum('type', ['article', 'consommable'])->default('article');
            // quantite = 1 pour un article, X pour un consommable
            $table->integer('quantite')->default(1);
            $table->text('observations')->nullable();
            $table->date('date_recuperation')->nullable();
            
            $table->foreignId('article_id')
                ->nullable()
                ->constrained('articles')
                ->onDelete('restrict');
            // Consommable — NULL si type = article
            $table->foreignId('consommable_id')
                ->nullable()
                ->constrained('consommables')
                ->onDelete('restrict');
            $table->foreignId('salle_id')->constrained('salles')->onDelete('cascade');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};
