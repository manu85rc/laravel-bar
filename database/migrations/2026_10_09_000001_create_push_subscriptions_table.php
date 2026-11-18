<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // MySQL no permite índices sobre columnas TEXT sin longitud de clave:
            // los endpoints FCM/APNS encajan en 500 caracteres.
            $table->string('endpoint', 500)->unique();
            $table->string('p256dh', 400);
            $table->string('auth', 200);
            $table->string('content_encoding', 32)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
