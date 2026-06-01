<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            $table->integer('pos_x')->default(0);
            $table->integer('pos_y')->default(0);
            $table->string('forma', 20)->default('circle');
            $table->integer('ancho')->default(80);
            $table->integer('alto')->default(80);
        });
    }

    public function down(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            $table->dropColumn(['pos_x', 'pos_y', 'forma', 'ancho', 'alto']);
        });
    }
};
