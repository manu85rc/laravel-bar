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
        });

        DB::statement('ALTER TABLE reservas MODIFY COLUMN mesa_id BIGINT UNSIGNED NULL');

        Schema::table('reservas', function (Blueprint $table) {
            $table->foreign('mesa_id')->references('id')->on('mesas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign(['mesa_id']);
        });

        DB::statement('ALTER TABLE reservas MODIFY COLUMN mesa_id BIGINT UNSIGNED NOT NULL');

        Schema::table('reservas', function (Blueprint $table) {
            $table->foreign('mesa_id')->references('id')->on('mesas')->onDelete('cascade');
        });
    }
};
