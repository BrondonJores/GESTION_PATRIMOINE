<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rapports', function (Blueprint $table): void {
            $table->dateTime('periode_debut')->nullable()->after('format');
            $table->dateTime('periode_fin')->nullable()->after('periode_debut');
        });
    }

    public function down(): void
    {
        Schema::table('rapports', function (Blueprint $table): void {
            $table->dropColumn(['periode_debut', 'periode_fin']);
        });
    }
};
