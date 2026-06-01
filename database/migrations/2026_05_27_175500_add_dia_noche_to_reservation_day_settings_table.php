<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_day_settings', function (Blueprint $table) {
            $table->time('hora_apertura_dia')->nullable()->after('hora_apertura');
            $table->time('hora_cierre_dia')->nullable()->after('hora_apertura_dia');
            $table->time('hora_apertura_noche')->nullable()->after('hora_cierre_dia');
            $table->time('hora_cierre_noche')->nullable()->after('hora_apertura_noche');
        });

        DB::statement('UPDATE reservation_day_settings SET
            hora_apertura_dia = hora_apertura,
            hora_cierre_dia = COALESCE(hora_cierre_siesta, hora_cierre),
            hora_apertura_noche = hora_apertura_siesta,
            hora_cierre_noche = CASE WHEN hora_apertura_siesta IS NOT NULL THEN hora_cierre ELSE NULL END
        ');
    }

    public function down(): void
    {
        Schema::table('reservation_day_settings', function (Blueprint $table) {
            $table->dropColumn(['hora_apertura_dia', 'hora_cierre_dia', 'hora_apertura_noche', 'hora_cierre_noche']);
        });
    }
};
