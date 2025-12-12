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
            ['nombre' => 'LIA', 'descripcion' => 'Laboratorio de Informática Aplicada'],
            ['nombre' => 'Lab FB', 'descripcion' => 'Laboratorio Facultad de Biología'],
            ['nombre' => 'Lab PM', 'descripcion' => 'Laboratorio Planta de Minerales'],
            ['nombre' => 'Lab B', 'descripcion' => 'Laboratorio B'],
            ['nombre' => 'Lab C', 'descripcion' => 'Laboratorio C'],
            ['nombre' => 'Lab Hardware', 'descripcion' => 'Laboratorio de Hardware'],
            ['nombre' => 'Of Hardware', 'descripcion' => 'Oficina de Hardware'],
            ['nombre' => 'Lab Redes', 'descripcion' => 'Laboratorio de Redes'],
            ['nombre' => 'Administracion', 'descripcion' => 'Oficina de Administración'],
            ['nombre' => 'Prestado', 'descripcion' => 'Elemento prestado a terceros'],
            ['nombre' => 'Dado de baja', 'descripcion' => 'Elemento dado de baja'],
        ];

        foreach ($ubicaciones as $ubicacion) {
            Ubicacion::create($ubicacion);
        }
    }
}
