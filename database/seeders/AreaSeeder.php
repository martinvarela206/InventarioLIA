<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            [
                'nombre' => 'Hardware',
                'descripcion' => 'Mantenimiento físico, placas madres, fuentes, periféricos y microelectrónica.',
                'color' => 'amber',
                'activo' => true,
            ],
            [
                'nombre' => 'Software',
                'descripcion' => 'Sistemas operativos, licencias, controladores, clonación e imágenes de disco.',
                'color' => 'blue',
                'activo' => true,
            ],
            [
                'nombre' => 'TI / Infraestructura',
                'descripcion' => 'Redes troncales, patcheras, switches, puntos de acceso Wi-Fi y servidores.',
                'color' => 'purple',
                'activo' => true,
            ],
            [
                'nombre' => 'General',
                'descripcion' => 'Consultas generales, inventario general, logística y soporte de cátedras.',
                'color' => 'emerald',
                'activo' => true,
            ],
        ];

        foreach ($areas as $a) {
            Area::firstOrCreate(['nombre' => $a['nombre']], $a);
        }
    }
}
