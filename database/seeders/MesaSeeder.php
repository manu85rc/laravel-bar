<?php

namespace Database\Seeders;

use App\Models\Mesa;
use App\Models\Zona;
use Illuminate\Database\Seeder;

class MesaSeeder extends Seeder
{
    public function run(): void
    {
        $zona = Zona::firstOrCreate(['nombre' => 'Planta Baja']);

        Mesa::create([
            'numero' => 'M1',
            'zona_id' => $zona->id,
            'disponible' => true,
            'pos_x' => 30,
            'pos_y' => 30,
            'forma' => 'circle',
            'ancho' => 80,
            'alto' => 80,
        ]);

        Mesa::create([
            'numero' => 'M2',
            'zona_id' => $zona->id,
            'disponible' => true,
            'pos_x' => 170,
            'pos_y' => 30,
            'forma' => 'square',
            'ancho' => 80,
            'alto' => 80,
        ]);

        Mesa::create([
            'numero' => 'M3',
            'zona_id' => $zona->id,
            'disponible' => true,
            'pos_x' => 30,
            'pos_y' => 170,
            'forma' => 'square',
            'ancho' => 120,
            'alto' => 80,
        ]);

        Mesa::create([
            'numero' => 'M4',
            'zona_id' => $zona->id,
            'disponible' => true,
            'pos_x' => 200,
            'pos_y' => 170,
            'forma' => 'circle',
            'ancho' => 100,
            'alto' => 100,
        ]);
    }
}
