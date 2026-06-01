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
        Schema::table('reservation_settings', function (Blueprint $table) {
            $table->time('hora_cierre_siesta')->nullable()->after('hora_cierre');
            $table->time('hora_apertura_siesta')->nullable()->after('hora_cierre_siesta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservation_settings', function (Blueprint $table) {
            $table->dropColumn(['hora_cierre_siesta', 'hora_apertura_siesta']);
        });
    }
};
