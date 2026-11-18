<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained()->onDelete('cascade');
            $table->foreignId('pedido_producto_id')->constrained('pedido_producto')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['pago_id', 'pedido_producto_id']);
            $table->unique('pedido_producto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_producto');
    }
};
