<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mercado_pago_settings', function (Blueprint $table) {
            $table->id();
            $table->text('access_token')->nullable();
            $table->boolean('mostrar_fecha')->default(true);
            $table->boolean('mostrar_id')->default(true);
            $table->boolean('mostrar_estado')->default(true);
            $table->boolean('mostrar_metodo')->default(true);
            $table->boolean('mostrar_tipo')->default(true);
            $table->boolean('mostrar_descripcion')->default(true);
            $table->boolean('mostrar_monto')->default(true);
            $table->boolean('mostrar_detalle')->default(true);
            $table->json('estados')->nullable();
            $table->unsignedTinyInteger('limite')->default(50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mercado_pago_settings');
    }
};
