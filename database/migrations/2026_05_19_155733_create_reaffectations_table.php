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
    Schema::create('reaffectations', function (Blueprint $table) {
        $table->id();
        $table->integer('quantite');
        $table->text('observations')->nullable();
        $table->date('date_reaffectation')->nullable();
        $table->foreignId('affectation_id')->constrained('affectations')->onDelete('cascade');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reaffectations');
    }
};
