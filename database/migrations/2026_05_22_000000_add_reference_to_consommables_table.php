<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('consommables', 'reference')) {
            return;
        }

        Schema::table('consommables', function (Blueprint $table) {
            $table->string('reference', 100)->nullable()->unique();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('consommables', 'reference')) {
            return;
        }

        Schema::table('consommables', function (Blueprint $table) {
            $table->dropUnique(['reference']);
            $table->dropColumn('reference');
        });
    }
};
