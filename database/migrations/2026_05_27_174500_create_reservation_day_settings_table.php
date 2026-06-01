<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_day_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_setting_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('dia');
            $table->boolean('activo')->default(true);
            $table->time('hora_apertura')->default('08:00');
            $table->time('hora_cierre')->default('23:00');
            $table->time('hora_cierre_siesta')->nullable();
            $table->time('hora_apertura_siesta')->nullable();
            $table->timestamps();

            $table->unique(['reservation_setting_id', 'dia'], 'unique_setting_day');
        });

        $settings = DB::table('reservation_settings')->first();
        if ($settings) {
            $activos = explode(',', $settings->dias_activos ?? '1,2,3,4,5,6,7');
            foreach (range(1, 7) as $dia) {
                DB::table('reservation_day_settings')->insert([
                    'reservation_setting_id' => $settings->id,
                    'dia' => $dia,
                    'activo' => in_array((string) $dia, $activos),
                    'hora_apertura' => $settings->hora_apertura ?? '08:00',
                    'hora_cierre' => $settings->hora_cierre ?? '23:00',
                    'hora_cierre_siesta' => $settings->hora_cierre_siesta,
                    'hora_apertura_siesta' => $settings->hora_apertura_siesta,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_day_settings');
    }
};
