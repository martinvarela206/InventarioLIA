<?php

namespace Database\Seeders;

use App\Models\AmbitoTicket;
use Illuminate\Database\Seeder;

class AmbitoTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ambitos = [
            [
                'nombre' => 'Laboratorio A',
                'descripcion' => 'Instalaciones, puestos y equipamiento general del Laboratorio A',
                'orden' => 1,
                'activo' => true,
            ],
            [
                'nombre' => 'Laboratorio B',
                'descripcion' => 'Instalaciones, puestos y equipamiento general del Laboratorio B',
                'orden' => 2,
                'activo' => true,
            ],
            [
                'nombre' => 'LIA General (Infraestructura / Servicios)',
                'descripcion' => 'Cuestiones edilicias, climatización, energía o servicios comunes del LIA',
                'orden' => 3,
                'activo' => true,
            ],
            [
                'nombre' => 'Redes y Conectividad',
                'descripcion' => 'Cableado estructurado, racks, switches, Wi-Fi o enlaces del laboratorio',
                'orden' => 4,
                'activo' => true,
            ],
            [
                'nombre' => 'Mobiliario y Espacios Físicos',
                'descripcion' => 'Bancos, sillas, armarios, pizarras o puestos de trabajo',
                'orden' => 5,
                'activo' => true,
            ],
            [
                'nombre' => 'Otros',
                'descripcion' => 'Incidencias generales no categorizadas en los ámbitos anteriores',
                'orden' => 6,
                'activo' => true,
            ],
        ];

        foreach ($ambitos as $ambito) {
            AmbitoTicket::firstOrCreate(
                ['nombre' => $ambito['nombre']],
                $ambito
            );
        }
    }
}
