<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create zonas from distinct piso values
        $pisos = DB::table('mesas')->whereNotNull('piso')->distinct()->pluck('piso')->toArray();
        foreach ($pisos as $piso) {
            DB::table('zonas')->insert([
                'nombre' => $piso,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Add zona_id column
        Schema::table('mesas', function (Blueprint $table) {
            $table->foreignId('zona_id')->nullable()->after('ubicacion')->constrained('zonas')->nullOnDelete();
        });

        // 3. Migrate data: set zona_id based on piso value
        $zonas = DB::table('zonas')->pluck('id', 'nombre')->toArray();
        $mesas = DB::table('mesas')->whereNotNull('piso')->get();
        foreach ($mesas as $mesa) {
            if (isset($zonas[$mesa->piso])) {
                DB::table('mesas')->where('id', $mesa->id)->update(['zona_id' => $zonas[$mesa->piso]]);
            }
        }

        // 4. Drop piso column
        Schema::table('mesas', function (Blueprint $table) {
            $table->dropColumn('piso');
        });
    }

    public function down(): void
    {
        // 1. Add piso column back
        Schema::table('mesas', function (Blueprint $table) {
            $table->string('piso', 50)->nullable()->default(null)->after('ubicacion');
        });

        // 2. Migrate data back
        $zonas = DB::table('zonas')->pluck('nombre', 'id')->toArray();
        $mesas = DB::table('mesas')->whereNotNull('zona_id')->get();
        foreach ($mesas as $mesa) {
            if (isset($zonas[$mesa->zona_id])) {
                DB::table('mesas')->where('id', $mesa->id)->update(['piso' => $zonas[$mesa->zona_id]]);
            }
        }

        // 3. Drop zona_id
        Schema::table('mesas', function (Blueprint $table) {
            $table->dropForeign(['zona_id']);
            $table->dropColumn('zona_id');
        });

        // 4. Drop zonas table
        Schema::dropIfExists('zonas');
    }
};
