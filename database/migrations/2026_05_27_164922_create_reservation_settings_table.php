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
        Schema::create('reservation_settings', function (Blueprint $table) {
            $table->id();
            $table->time('hora_apertura')->default('08:00');
            $table->time('hora_cierre')->default('23:00');
            $table->integer('intervalo_minutos')->default(15);
            $table->string('dias_activos')->default('1,2,3,4,5,6,7');
            $table->integer('max_personas')->default(20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_settings');
    }
};
