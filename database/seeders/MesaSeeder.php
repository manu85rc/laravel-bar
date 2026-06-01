<?php

namespace Database\Seeders;

use App\Models\Mesa;
use Illuminate\Database\Seeder;

class MesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample mesas
        Mesa::create([
            'numero' => 'M1',
            'capacidad' => 2,
            'ubicacion' => 'Interior',
            'piso' => 'Planta Baja',
            'disponible' => true,
            'pos_x' => 30,
            'pos_y' => 30,
            'forma' => 'circle',
            'ancho' => 80,
            'alto' => 80,
        ]);

        Mesa::create([
            'numero' => 'M2',
            'capacidad' => 4,
            'ubicacion' => 'Terraza',
            'piso' => 'Planta Baja',
            'disponible' => true,
            'pos_x' => 170,
            'pos_y' => 30,
            'forma' => 'square',
            'ancho' => 80,
            'alto' => 80,
        ]);

        Mesa::create([
            'numero' => 'M3',
            'capacidad' => 6,
            'ubicacion' => 'Interior',
            'piso' => 'Planta Baja',
            'disponible' => true,
            'pos_x' => 30,
            'pos_y' => 170,
            'forma' => 'square',
            'ancho' => 120,
            'alto' => 80,
        ]);

        Mesa::create([
            'numero' => 'M4',
            'capacidad' => 8,
            'ubicacion' => 'Terraza',
            'piso' => 'Planta Baja',
            'disponible' => true,
            'pos_x' => 200,
            'pos_y' => 170,
            'forma' => 'circle',
            'ancho' => 100,
            'alto' => 100,
        ]);
    }
}
