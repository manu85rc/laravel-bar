<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mercado_pago_settings', function (Blueprint $table) {
            $table->boolean('mostrar_pagador')->default(true)->after('mostrar_id');
        });
    }

    public function down(): void
    {
        Schema::table('mercado_pago_settings', function (Blueprint $table) {
            $table->dropColumn('mostrar_pagador');
        });
    }
};
