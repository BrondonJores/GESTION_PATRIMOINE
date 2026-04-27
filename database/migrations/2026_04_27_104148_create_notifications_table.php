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
    Schema::create('notifications', function (Blueprint $table) {
        $table->id();
        $table->enum('canal', ['Email', 'SMS', 'InApp', 'Tous'])->default('Tous');
        $table->text('contenu');
        $table->boolean('lu')->default(false);
        $table->datetime('date_envoi');
        $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
