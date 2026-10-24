<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ubicacion;

class UbicacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ubicaciones = [
            ['nombre' => 'Oficina A', 'descripcion' => 'Oficina A del LIA'],
            ['nombre' => 'Oficina B', 'descripcion' => 'Oficina B del LIA'],
            ['nombre' => 'Laboratorio Federico Sanchez', 'descripcion' => 'Laboratorio Principal Federico Sánchez'],
            ['nombre' => 'Laboratorio Paola Moyano', 'descripcion' => 'Laboratorio Paola Moyano'],
            ['nombre' => 'Laboratorio Redes', 'descripcion' => 'Laboratorio de Telecomunicaciones y Redes'],
            ['nombre' => 'Armario de HW', 'descripcion' => 'Armario de almacenamiento de Hardware y Repuestos'],
            ['nombre' => 'Administracion', 'descripcion' => 'Oficina de Administración y Secretaría'],
            ['nombre' => 'Laboratorio B', 'descripcion' => 'Laboratorio de Computación B'],
            ['nombre' => 'Laboratorio C', 'descripcion' => 'Laboratorio de Computación C'],
            ['nombre' => 'Rack Administracion', 'descripcion' => 'Rack de servidores de Administración'],
            ['nombre' => 'Rack Redes', 'descripcion' => 'Rack de equipamiento de Redes'],
            ['nombre' => 'Oficina de Hardware', 'descripcion' => 'Taller y Oficina técnica de Hardware'],
        ];

        foreach ($ubicaciones as $ubicacion) {
            Ubicacion::firstOrCreate(['nombre' => $ubicacion['nombre']], $ubicacion);
        }
    }
}
