<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reaffectations', function (Blueprint $table) {
            $table->foreignId('salle_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reaffectations', function (Blueprint $table) {
            $table->dropForeign(['salle_id']);
            $table->dropColumn('salle_id');
        });
    }
};
