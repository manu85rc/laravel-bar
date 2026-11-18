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
        Schema::create('offline_sync', function (Blueprint $table) {
            $table->id();
            $table->string('client_id')->unique()->index(); // UUID generado en el navegador
            $table->string('type')->default('pedido');      // tipo de operación
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('server_id')->nullable(); // id del registro creado en el servidor
            $table->json('payload')->nullable();            // datos originales de la operación
            $table->string('estado')->default('sincronizado'); // sincronizado / error
            $table->string('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_sync');
    }
};
