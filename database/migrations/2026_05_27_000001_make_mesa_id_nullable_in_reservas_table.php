<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign(['mesa_id']);
            $table->unsignedBigInteger('mesa_id')->nullable()->change();
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->foreign('mesa_id')->references('id')->on('mesas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign(['mesa_id']);
            $table->unsignedBigInteger('mesa_id')->change();
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->foreign('mesa_id')->references('id')->on('mesas')->onDelete('cascade');
        });
    }
};
