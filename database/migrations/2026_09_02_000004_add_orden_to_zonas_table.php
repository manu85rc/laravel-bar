<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zonas', function (Blueprint $table) {
            $table->unsignedInteger('orden')->default(0)->after('nombre');
        });

        // Establish an initial order based on current name (alphabetical)
        $zonas = DB::table('zonas')->orderBy('nombre')->get();
        foreach ($zonas as $i => $zona) {
            DB::table('zonas')->where('id', $zona->id)->update(['orden' => $i + 1]);
        }
    }

    public function down(): void
    {
        Schema::table('zonas', function (Blueprint $table) {
            $table->dropColumn('orden');
        });
    }
};
