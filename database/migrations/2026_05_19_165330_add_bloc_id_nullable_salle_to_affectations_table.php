<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->foreignId('bloc_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('salle_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->dropForeign(['bloc_id']);
            $table->dropColumn('bloc_id');
            $table->foreignId('salle_id')->nullable(false)->change();
        });
    }
};
