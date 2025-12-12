<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tipo;

class TipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'cpu', 'descripcion' => 'Computadora de escritorio'],
            ['nombre' => 'monitor', 'descripcion' => 'Monitor o pantalla'],
            ['nombre' => 'switch', 'descripcion' => 'Switch de red'],
            ['nombre' => 'router', 'descripcion' => 'Router o enrutador'],
            ['nombre' => 'impresora', 'descripcion' => 'Impresora'],
            ['nombre' => 'teclado', 'descripcion' => 'Teclado'],
            ['nombre' => 'mouse', 'descripcion' => 'Mouse o ratón'],
            ['nombre' => 'proyector', 'descripcion' => 'Proyector'],
            ['nombre' => 'disco', 'descripcion' => 'Disco duro o SSD'],
            ['nombre' => 'memoria', 'descripcion' => 'Memoria RAM'],
            ['nombre' => 'otro', 'descripcion' => 'Otro tipo de elemento'],
        ];

        foreach ($tipos as $tipo) {
            Tipo::create($tipo);
        }
    }
}
