<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Mesa;

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
            'disponible' => true,
        ]);

        Mesa::create([
            'numero' => 'M2',
            'capacidad' => 4,
            'ubicacion' => 'Terraza',
            'disponible' => true,
        ]);

        Mesa::create([
            'numero' => 'M3',
            'capacidad' => 6,
            'ubicacion' => 'Interior',
            'disponible' => true,
        ]);

        Mesa::create([
            'numero' => 'M4',
            'capacidad' => 8,
            'ubicacion' => 'Terraza',
            'disponible' => true,
        ]);
    }
}
