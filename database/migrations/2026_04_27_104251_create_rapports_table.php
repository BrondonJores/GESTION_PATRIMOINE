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
    Schema::create('rapports', function (Blueprint $table) {
        $table->id();
        $table->string('type_rapport', 100);
        $table->string('chemin_fichier', 255)->nullable();
        $table->enum('format', ['PDF', 'Excel'])->default('PDF');
        $table->datetime('date_generation');
        $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};
