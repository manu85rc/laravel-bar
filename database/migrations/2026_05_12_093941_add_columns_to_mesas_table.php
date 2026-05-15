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
        Schema::table("mesas", function (Blueprint $table) {
            $table->string("numero", 10)->unique();
            $table->integer("capacidad");
            $table->string("ubicacion", 50)->nullable();
            $table->boolean("disponible")->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("mesas", function (Blueprint $table) {
            $table->dropColumn(["numero", "capacidad", "ubicacion", "disponible"]);
        });
    }
};