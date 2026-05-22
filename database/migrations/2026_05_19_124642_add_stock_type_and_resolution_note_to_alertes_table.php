<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alertes', function (Blueprint $table): void {
            $table->string('type_alerte')->default('stock_minimal_atteint')->after('canal');
            $table->text('note_resolution')->nullable()->after('retour');
        });
    }

    public function down(): void
    {
        Schema::table('alertes', function (Blueprint $table): void {
            $table->dropColumn(['type_alerte', 'note_resolution']);
        });
    }
};
